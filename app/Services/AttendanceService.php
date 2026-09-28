<?php

namespace App\Services;

use App\Enums\DocumentCategory;
use App\Models\CoachingSession;
use App\Models\CoachingSessionAttendee;
use App\Models\Cohort;
use App\Models\Document;
use App\Models\Project;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceService
{
    /**
     * Peuple les présences d'une séance à partir des startups inscrites dans sa cohorte.
     * Renvoie le nombre de startups nouvellement ajoutées.
     */
    public function populateFromCohort(CoachingSession $session): int
    {
        if (! $session->cohort_id) {
            return 0;
        }

        $session->loadMissing('cohort.enrollments.project');
        $enrolledProjects = $session->cohort?->enrollments?->pluck('project')->filter() ?? collect();

        $existingProjectIds = $session->attendees()->pluck('project_id')->all();

        $addedCount = 0;
        foreach ($enrolledProjects as $project) {
            if (! in_array($project->id, $existingProjectIds, true)) {
                CoachingSessionAttendee::create([
                    'coaching_session_id' => $session->id,
                    'project_id' => $project->id,
                    'attended' => false,
                ]);
                $addedCount++;
            }
        }

        return $addedCount;
    }

    /**
     * Génère le fichier CSV (compatible Microsoft Excel) d'émargement d'une séance.
     * Contient le BOM UTF-8 et utilise le séparateur point-virgule (;).
     */
    public function generateSessionAttendanceCsv(CoachingSession $session): string
    {
        $session->loadMissing([
            'attendees.project.entrepreneur',
            'cohort.enrollments.project.entrepreneur',
            'project.entrepreneur',
        ]);

        $attendees = $session->attendees;
        $rows = [];

        if ($attendees->isNotEmpty()) {
            foreach ($attendees as $attendee) {
                $project = $attendee->project;
                if (! $project) {
                    continue;
                }
                $rows[] = [
                    'id_projet' => $project->id,
                    'nom_startup' => $project->name,
                    'entrepreneur' => $project->entrepreneur?->full_name ?? '',
                    'email' => $project->entrepreneur?->email ?? '',
                    'telephone' => $project->entrepreneur?->phone ?? '',
                    'presence' => $attendee->attended ? 'OUI' : 'NON',
                    'observations' => $attendee->notes ?? '',
                    'retour_startup' => $attendee->feedback ?? '',
                ];
            }
        } elseif ($session->cohort_id && $session->cohort) {
            foreach ($session->cohort->enrollments as $enrollment) {
                $project = $enrollment->project;
                if (! $project) {
                    continue;
                }
                $rows[] = [
                    'id_projet' => $project->id,
                    'nom_startup' => $project->name,
                    'entrepreneur' => $project->entrepreneur?->full_name ?? '',
                    'email' => $project->entrepreneur?->email ?? '',
                    'telephone' => $project->entrepreneur?->phone ?? '',
                    'presence' => 'NON',
                    'observations' => '',
                    'retour_startup' => '',
                ];
            }
        } elseif ($session->project) {
            $project = $session->project;
            $rows[] = [
                'id_projet' => $project->id,
                'nom_startup' => $project->name,
                'entrepreneur' => $project->entrepreneur?->full_name ?? '',
                'email' => $project->entrepreneur?->email ?? '',
                'telephone' => $project->entrepreneur?->phone ?? '',
                'presence' => 'NON',
                'observations' => '',
                'retour_startup' => '',
            ];
        }

        return $this->formatCsv($rows);
    }

    /**
     * Télécharge le fichier CSV d'émargement d'une séance.
     */
    public function downloadSessionAttendanceCsv(CoachingSession $session): StreamedResponse
    {
        $csvContent = $this->generateSessionAttendanceCsv($session);
        $themeSlug = Str::slug($session->theme ?? 'seance');
        $dateStr = $session->date ? $session->date->format('Y-m-d') : date('Y-m-d');
        $filename = "emargement-seance-{$session->id}-{$dateStr}-{$themeSlug}.csv";

        return response()->streamDownload(function () use ($csvContent) {
            echo $csvContent;
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Génère la matrice complète d'émargement de toutes les séances d'une cohorte.
     */
    public function generateCohortAttendanceMatrixCsv(Cohort $cohort): string
    {
        $cohort->loadMissing(['enrollments.project.entrepreneur', 'coachingSessions.attendees']);

        $sessions = $cohort->coachingSessions()->orderBy('date')->get();
        $enrollments = $cohort->enrollments;

        $header = [
            'ID_PROJET',
            'NOM_STARTUP',
            'ENTREPRENEUR',
            'EMAIL',
        ];

        foreach ($sessions as $index => $s) {
            $label = 'S'.($index + 1).' ('.($s->date ? $s->date->format('d/m/Y') : 'N/D').')';
            $header[] = $label;
        }

        $header[] = 'TOTAL_PRESENCES';
        $header[] = 'TOTAL_SEANCES';
        $header[] = 'TAUX_ASSIDUITE_%';

        $totalSessionsCount = $sessions->count();

        // BOM UTF-8
        $output = "\xEF\xBB\xBF";
        $output .= implode(';', array_map([$this, 'escapeCsvCell'], $header))."\r\n";

        foreach ($enrollments as $enrollment) {
            $project = $enrollment->project;
            if (! $project) {
                continue;
            }

            $line = [
                $project->id,
                $project->name,
                $project->entrepreneur?->full_name ?? '',
                $project->entrepreneur?->email ?? '',
            ];

            $presencesCount = 0;

            foreach ($sessions as $s) {
                $att = $s->attendees->firstWhere('project_id', $project->id);
                if ($att) {
                    if ($att->attended) {
                        $line[] = 'OUI';
                        $presencesCount++;
                    } else {
                        $line[] = 'NON';
                    }
                } else {
                    $line[] = '—';
                }
            }

            $rate = $totalSessionsCount > 0 ? round(($presencesCount / $totalSessionsCount) * 100, 1) : 0;

            $line[] = $presencesCount;
            $line[] = $totalSessionsCount;
            $line[] = $rate.'%';

            $output .= implode(';', array_map([$this, 'escapeCsvCell'], $line))."\r\n";
        }

        return $output;
    }

    /**
     * Télécharge la matrice globale des présences d'une cohorte.
     */
    public function downloadCohortAttendanceMatrixCsv(Cohort $cohort): StreamedResponse
    {
        $csvContent = $this->generateCohortAttendanceMatrixCsv($cohort);
        $cohortSlug = Str::slug($cohort->name);
        $filename = "matrice-presences-cohorte-{$cohort->id}-{$cohortSlug}.csv";

        return response()->streamDownload(function () use ($csvContent) {
            echo $csvContent;
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Importe un fichier CSV d'émargement pour mettre à jour la liste des présences.
     * Supporte les séparateurs ';' ou ',' ou '\t'.
     *
     * @return array{total: int, present: int, absent: int, updated: int, errors: array<string>}
     */
    public function importSessionAttendanceCsv(CoachingSession $session, string $rawContentOrPath): array
    {
        $content = file_exists($rawContentOrPath) ? file_get_contents($rawContentOrPath) : $rawContentOrPath;

        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            $content = substr($content, 3);
        }

        $lines = preg_split("/\r\n|\n|\r/", trim($content));
        if (empty($lines)) {
            return ['total' => 0, 'present' => 0, 'absent' => 0, 'updated' => 0, 'errors' => ['Le fichier est vide.']];
        }

        $firstLine = $lines[0];
        $delimiter = ';';
        if (substr_count($firstLine, ';') < substr_count($firstLine, ',')) {
            $delimiter = ',';
        } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ';')) {
            $delimiter = "\t";
        }

        $headerCells = str_getcsv($firstLine, $delimiter);
        $headerMap = [];
        foreach ($headerCells as $idx => $cell) {
            $norm = mb_strtolower(trim($cell));
            $norm = str_replace(['(', ')', '/', '\\', '_', '-'], ' ', $norm);
            $norm = preg_replace('/\s+/', ' ', $norm);

            if (str_contains($norm, 'id projet') || $norm === 'id') {
                $headerMap['id_projet'] = $idx;
            } elseif (str_contains($norm, 'retour') || str_contains($norm, 'feedback')) {
                $headerMap['retour_startup'] = $idx;
            } elseif (str_contains($norm, 'observation') || str_contains($norm, 'note') || str_contains($norm, 'commentaire')) {
                $headerMap['observations'] = $idx;
            } elseif (str_contains($norm, 'presence') || str_contains($norm, 'present') || str_contains($norm, 'statut')) {
                $headerMap['presence'] = $idx;
            } elseif (str_contains($norm, 'email') || str_contains($norm, 'courriel') || str_contains($norm, 'mail')) {
                $headerMap['email'] = $idx;
            } elseif (str_contains($norm, 'startup') || str_contains($norm, 'projet') || str_contains($norm, 'nom')) {
                $headerMap['nom_startup'] = $idx;
            }
        }

        $stats = [
            'total' => 0,
            'present' => 0,
            'absent' => 0,
            'updated' => 0,
            'errors' => [],
        ];

        for ($i = 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if ($line === '') {
                continue;
            }

            $cells = str_getcsv($line, $delimiter);
            $stats['total']++;

            $projectId = null;

            // 1. Tenter par ID_PROJET
            if (isset($headerMap['id_projet'], $cells[$headerMap['id_projet']]) && is_numeric(trim($cells[$headerMap['id_projet']]))) {
                $candidateId = (int) trim($cells[$headerMap['id_projet']]);
                if (Project::where('sae_id', $session->sae_id)->where('id', $candidateId)->exists()) {
                    $projectId = $candidateId;
                }
            }

            // 2. Tenter par nom de startup
            if (! $projectId && isset($headerMap['nom_startup'], $cells[$headerMap['nom_startup']])) {
                $name = trim($cells[$headerMap['nom_startup']]);
                if ($name !== '') {
                    $project = Project::where('sae_id', $session->sae_id)->where('name', $name)->first();
                    if ($project) {
                        $projectId = $project->id;
                    }
                }
            }

            // 3. Tenter par email entrepreneur
            if (! $projectId && isset($headerMap['email'], $cells[$headerMap['email']])) {
                $email = trim($cells[$headerMap['email']]);
                if ($email !== '') {
                    $project = Project::where('sae_id', $session->sae_id)
                        ->whereHas('entrepreneur', fn ($q) => $q->where('email', $email))
                        ->first();
                    if ($project) {
                        $projectId = $project->id;
                    }
                }
            }

            if (! $projectId) {
                $ident = $cells[$headerMap['nom_startup'] ?? 0] ?? "Ligne {$i}";
                $stats['errors'][] = "Impossible d'identifier la startup pour : {$ident}";

                continue;
            }

            // Déterminer la présence
            $presenceRaw = isset($headerMap['presence'], $cells[$headerMap['presence']]) ? mb_strtolower(trim($cells[$headerMap['presence']])) : '';
            $isPresent = in_array($presenceRaw, ['oui', 'yes', '1', 'true', 'vrai', 'present', 'présent', 'p'], true);

            $notes = isset($headerMap['observations'], $cells[$headerMap['observations']]) ? trim($cells[$headerMap['observations']]) : null;
            $feedback = isset($headerMap['retour_startup'], $cells[$headerMap['retour_startup']]) ? trim($cells[$headerMap['retour_startup']]) : null;

            $updateData = ['attended' => $isPresent];
            if ($notes !== null && $notes !== '') {
                $updateData['notes'] = $notes;
            }
            if ($feedback !== null && $feedback !== '') {
                $updateData['feedback'] = $feedback;
            }

            CoachingSessionAttendee::updateOrCreate(
                [
                    'coaching_session_id' => $session->id,
                    'project_id' => $projectId,
                ],
                $updateData
            );

            if ($isPresent) {
                $stats['present']++;
            } else {
                $stats['absent']++;
            }
            $stats['updated']++;
        }

        return $stats;
    }

    /**
     * Attache un fichier de feuille d'émargement signée ou complétée comme document/livrable GED.
     */
    public function attachSignedDocument(
        CoachingSession $session,
        string $storedPath,
        string $originalName,
        ?string $title = null,
        ?int $userId = null
    ): Document {
        $disk = 'public';
        $maxVersion = Document::where('documentable_type', CoachingSession::class)
            ->where('documentable_id', $session->id)
            ->max('version') ?? 0;
        $version = $maxVersion + 1;

        $size = Storage::disk($disk)->exists($storedPath) ? Storage::disk($disk)->size($storedPath) : null;
        $mimeType = Storage::disk($disk)->exists($storedPath) ? Storage::disk($disk)->mimeType($storedPath) : null;

        return Document::create([
            'sae_id' => $session->sae_id,
            'documentable_type' => CoachingSession::class,
            'documentable_id' => $session->id,
            'category' => DocumentCategory::LIVRABLE,
            'title' => $title ?: "Feuille d'émargement signée (v{$version})",
            'disk' => $disk,
            'path' => $storedPath,
            'original_name' => $originalName,
            'mime_type' => $mimeType,
            'size' => $size,
            'version' => $version,
            'uploaded_by_id' => $userId,
        ]);
    }

    /**
     * Formate un tableau de lignes en CSV UTF-8 avec point-virgule.
     */
    private function formatCsv(array $rows): string
    {
        $output = "\xEF\xBB\xBF"; // UTF-8 BOM
        $headers = [
            'ID_PROJET',
            'NOM_STARTUP',
            'ENTREPRENEUR',
            'EMAIL',
            'TELEPHONE',
            'PRESENCE (OUI/NON)',
            'OBSERVATIONS',
            'RETOUR_STARTUP',
        ];

        $output .= implode(';', array_map([$this, 'escapeCsvCell'], $headers))."\r\n";

        foreach ($rows as $row) {
            $line = [
                $row['id_projet'],
                $row['nom_startup'],
                $row['entrepreneur'],
                $row['email'],
                $row['telephone'],
                $row['presence'],
                $row['observations'],
                $row['retour_startup'],
            ];
            $output .= implode(';', array_map([$this, 'escapeCsvCell'], $line))."\r\n";
        }

        return $output;
    }

    private function escapeCsvCell(mixed $value): string
    {
        $val = (string) ($value ?? '');
        $val = str_replace('"', '""', $val);

        return '"'.$val.'"';
    }
}
