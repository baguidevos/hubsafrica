<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic Stratégique & Performance — {{ $tenant->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #0f172a;
            background-color: #f8fafc;
            line-height: 1.5;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            padding-bottom: 40px;
        }

        .no-print-toolbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #0f172a;
            color: #ffffff;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: #0d9488;
            color: #ffffff;
        }
        .btn-primary:hover {
            background-color: #0f766e;
        }

        .btn-secondary {
            background-color: rgba(255,255,255,0.15);
            color: #ffffff;
        }
        .btn-secondary:hover {
            background-color: rgba(255,255,255,0.25);
        }

        .report-sheet {
            max-width: 1080px;
            margin: 24px auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 8px 24px rgba(0,0,0,0.04);
            padding: 48px;
            border: 1px solid #e2e8f0;
        }

        /* En-tête officiel */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 24px;
            border-bottom: 2px solid #0d9488;
            gap: 24px;
        }

        .tenant-brand {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .tenant-logo-img {
            max-height: 72px;
            max-width: 160px;
            object-fit: contain;
            border-radius: 8px;
        }

        .tenant-logo-fallback {
            width: 64px;
            height: 64px;
            background: #0d9488;
            color: #ffffff;
            font-weight: 800;
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            letter-spacing: 1px;
        }

        .tenant-info h1 {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
        }

        .tenant-info p {
            font-size: 11px;
            color: #64748b;
            margin-top: 4px;
        }

        .hub-brand {
            text-align: right;
            font-size: 11px;
            color: #64748b;
        }

        .hub-brand-badge {
            display: inline-block;
            background: #f0fdfa;
            color: #0d9488;
            border: 1px solid #ccfbf1;
            padding: 4px 10px;
            border-radius: 9999px;
            font-weight: 700;
            font-size: 11px;
            margin-bottom: 6px;
        }

        /* Titre du rapport */
        .report-title-box {
            margin-top: 28px;
            background: linear-gradient(135deg, #f0fdfa 0%, #e6fffa 100%);
            border: 1px solid #99f6e4;
            border-radius: 12px;
            padding: 20px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .report-title-box h2 {
            font-size: 18px;
            font-weight: 800;
            color: #115e59;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .report-title-box p {
            font-size: 12px;
            color: #0f766e;
            margin-top: 2px;
        }

        .meta-tags {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .meta-tag {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            color: #334155;
        }

        /* Sections du rapport */
        .section-block {
            margin-top: 32px;
        }

        .section-title {
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 8px;
        }

        /* Entonnoir */
        .funnel-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }

        .funnel-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            text-align: center;
            position: relative;
        }

        .funnel-step-num {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
        }

        .funnel-count {
            font-size: 26px;
            font-weight: 800;
            color: #0f172a;
            margin: 4px 0;
        }

        .funnel-label {
            font-size: 11px;
            font-weight: 600;
            color: #334155;
        }

        .funnel-pct {
            margin-top: 8px;
            font-size: 11px;
            font-weight: 700;
            color: #0d9488;
            background: #f0fdfa;
            padding: 2px 8px;
            border-radius: 9999px;
            display: inline-block;
        }

        /* Grilles 2 & 3 colonnes */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }

        .card-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px;
        }

        .card-box-header {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: #475569;
            margin-bottom: 12px;
        }

        .stat-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 12px;
        }

        .stat-row:last-child {
            border-bottom: none;
        }

        .stat-row-val {
            font-weight: 700;
            color: #0f172a;
        }

        /* Signatures */
        .signatures-section {
            margin-top: 48px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 48px;
            padding-top: 24px;
            border-top: 1px dashed #cbd5e1;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .signature-box {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            background: #ffffff;
            min-height: 130px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .signature-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #475569;
        }

        .signature-footer {
            font-size: 10px;
            color: #94a3b8;
            font-style: italic;
            border-top: 1px solid #f1f5f9;
            padding-top: 8px;
        }

        .report-footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
            padding-top: 16px;
        }

        /* Print styles */
        @media print {
            body {
                background: #ffffff;
                color: #000000;
                padding: 0;
            }

            .no-print-toolbar {
                display: none !important;
            }

            .report-sheet {
                box-shadow: none;
                border: none;
                margin: 0;
                padding: 0;
                max-width: 100%;
            }

            @page {
                size: A4 portrait;
                margin: 12mm;
            }

            .section-block {
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    {{-- Barre d'outils à l'écran (masquée à l'impression) --}}
    <div class="no-print-toolbar">
        <div>
            <strong>{{ $tenant->name }}</strong> &middot; Diagnostic &amp; Performance du Portefeuille
        </div>
        <div style="display: flex; gap: 10px;">
            <button onclick="window.print()" class="btn btn-primary">
                🖨️ Imprimer / Enregistrer en PDF
            </button>
            <a href="{{ route('sae.reports.analytics.download', ['tenant' => $tenant->slug, 'program_id' => $filters['program_id'] ?? 'all', 'cohort_id' => $filters['cohort_id'] ?? 'all']) }}" class="btn btn-secondary">
                ⬇️ Télécharger (.html)
            </a>
            <a href="{{ route('sae.reports.analytics.csv', ['tenant' => $tenant->slug, 'program_id' => $filters['program_id'] ?? 'all', 'cohort_id' => $filters['cohort_id'] ?? 'all']) }}" class="btn btn-secondary">
                📊 Exporter CSV
            </a>
            <button onclick="window.close()" class="btn btn-secondary">
                ✕ Fermer
            </button>
        </div>
    </div>

    {{-- Feuille A4 du Rapport --}}
    <div class="report-sheet">
        {{-- En-tête officiel avec Logo du Tenant --}}
        <header class="report-header">
            <div class="tenant-brand">
                @if(!empty($branding['src']))
                    <img src="{{ $branding['src'] }}" alt="{{ $tenant->name }}" class="tenant-logo-img">
                @else
                    <div class="tenant-logo-fallback">
                        {{ $branding['initials'] }}
                    </div>
                @endif
                <div class="tenant-info">
                    <h1>{{ $tenant->name }}</h1>
                    <p>
                        <strong>Structure d'Accompagnement à l'Entrepreneuriat Innovant (SAEI)</strong><br>
                        @if($tenant->address) {{ $tenant->address }} &middot; @endif
                        @if($tenant->city) {{ $tenant->city }} &middot; @endif
                        @if($tenant->email) {{ $tenant->email }} &middot; @endif
                        @if($tenant->phone) {{ $tenant->phone }} @endif
                    </p>
                </div>
            </div>

            <div class="hub-brand">
                @if(!empty($branding['hub_logo']))
                    <img src="{{ $branding['hub_logo'] }}" alt="Hubs Africa" style="max-height: 40px; object-fit: contain; margin-bottom: 4px; display: inline-block;">
                @else
                    <span class="hub-brand-badge">Plateforme Hubs Africa V2</span>
                @endif
                <p>
                    <strong>Date :</strong> {{ now()->format('d/m/Y H:i') }}<br>
                    <strong>Réf :</strong> RPT-PRF-{{ strtoupper(substr($tenant->slug, 0, 4)) }}-{{ now()->format('Ymd') }}<br>
                    <strong>Auteur :</strong> {{ $author ?? 'Direction des Programmes SAE' }}
                </p>
            </div>
        </header>

        {{-- Titre & Cadrage --}}
        <div class="report-title-box">
            <div>
                <h2>Diagnostic Stratégique &amp; Performance du Portefeuille</h2>
                <p>Analyse de sélectivité, inclusion, intensité d'accompagnement et financements</p>
            </div>
            <div class="meta-tags">
                <span class="meta-tag">Programme : {{ $filters['program_name'] ?? 'Tous' }}</span>
                <span class="meta-tag">Cohorte : {{ $filters['cohort_name'] ?? 'Toutes' }}</span>
                <span class="meta-tag">Portefeuille Global</span>
            </div>
        </div>

        {{-- 1. Entonnoir de Sélection & Conversion --}}
        <div class="section-block">
            <div class="section-title">
                <span>1. Entonnoir de Sélection &amp; Taux de Conversion</span>
            </div>
            <div class="funnel-grid">
                @foreach($data['funnel'] ?? [] as $idx => $step)
                    <div class="funnel-card">
                        <div class="funnel-step-num">Jalon {{ $idx + 1 }}</div>
                        <div class="funnel-count">{{ $step['count'] }}</div>
                        <div class="funnel-label">{{ $step['stage'] }}</div>
                        <div class="funnel-pct">{{ $step['pct'] }}% de conversion</div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- 2. Parité Genre & Inclusion Territoriale --}}
        <div class="section-block">
            <div class="section-title">
                <span>2. Parité Genre &amp; Diversité Territoriale</span>
            </div>
            <div class="grid-3">
                {{-- Genre --}}
                <div class="card-box">
                    <div class="card-box-header">Parité Genre Porteurs</div>
                    <div class="stat-row">
                        <span style="color: #db2777; font-weight: 600;">Femmes entrepreneures</span>
                        <span class="stat-row-val">{{ $data['femmes'] ?? 0 }} ({{ $data['pctFemmes'] ?? 0 }}%)</span>
                    </div>
                    <div class="stat-row">
                        <span style="color: #4f46e5; font-weight: 600;">Hommes entrepreneurs</span>
                        <span class="stat-row-val">{{ $data['hommes'] ?? 0 }} ({{ $data['pctHommes'] ?? 0 }}%)</span>
                    </div>
                    <div style="margin-top: 12px; font-size: 11px; color: #0d9488; font-weight: 700;">
                        Cible CUBE / Afric'innov : Min. 40% de femmes
                    </div>
                </div>

                {{-- Territoires --}}
                <div class="card-box">
                    <div class="card-box-header">Top Territoires d'Origine</div>
                    @forelse($data['territoires'] ?? [] as $t)
                        <div class="stat-row">
                            <span>{{ $t->territory }}</span>
                            <span class="stat-row-val">{{ $t->count }} porteur(s)</span>
                        </div>
                    @empty
                        <p style="font-size: 11px; color: #94a3b8; padding: 8px 0;">Données en cours de consolidation</p>
                    @endforelse
                </div>

                {{-- Niveaux d'études --}}
                <div class="card-box">
                    <div class="card-box-header">Niveaux de Formation</div>
                    @forelse($data['niveauxEtudes'] ?? [] as $n)
                        <div class="stat-row">
                            <span>{{ $n->education_level }}</span>
                            <span class="stat-row-val">{{ $n->count }}</span>
                        </div>
                    @empty
                        <p style="font-size: 11px; color: #94a3b8; padding: 8px 0;">Profils académiques en cours de saisie</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- 3. Intensité d'Accompagnement & Financements --}}
        <div class="section-block">
            <div class="section-title">
                <span>3. Intensité de l'Accompagnement &amp; Financements Mobilisés</span>
            </div>
            <div class="grid-2">
                {{-- Accompagnement --}}
                <div class="card-box">
                    <div class="card-box-header">Volume de Mentorat &amp; Ateliers</div>
                    <div class="stat-row">
                        <span>Séances d'accompagnement réalisées</span>
                        <span class="stat-row-val">{{ $data['totalSessions'] ?? 0 }} séances</span>
                    </div>
                    <div class="stat-row">
                        <span>Volume horaire cumulé délivré</span>
                        <span class="stat-row-val" style="color: #0d9488; font-size: 14px;">{{ $data['totalHeures'] ?? 0 }} heures</span>
                    </div>
                    <div style="margin-top: 10px; font-size: 11px; font-weight: 600; color: #475569;">
                        Ventilation par format d'intervention :
                    </div>
                    @foreach($data['typesSeance'] ?? [] as $type => $count)
                        <div class="stat-row">
                            <span style="color: #64748b;">{{ $type }}</span>
                            <span class="stat-row-val">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>

                {{-- Financements --}}
                <div class="card-box">
                    <div class="card-box-header">Fonds Mobilisés &amp; Levées Réalisées</div>
                    <div class="stat-row">
                        <span>Montant total sécurisé</span>
                        <span class="stat-row-val" style="color: #16a34a; font-size: 15px;">
                            {{ number_format((float) ($data['totalFinancement'] ?? 0), 0, ',', ' ') }} FCFA
                        </span>
                    </div>
                    <div style="margin-top: 10px; font-size: 11px; font-weight: 600; color: #475569;">
                        Ventilation par nature d'apport :
                    </div>
                    @forelse($data['financements'] ?? [] as $f)
                        @php
                            $typeLabel = $f->type instanceof \App\Enums\FundingType ? $f->type->getLabel() : ($f->type ?: 'Autre');
                        @endphp
                        <div class="stat-row">
                            <span style="color: #64748b;">{{ $typeLabel }}</span>
                            <span class="stat-row-val">{{ number_format((float) $f->total, 0, ',', ' ') }} FCFA</span>
                        </div>
                    @empty
                        <p style="font-size: 11px; color: #94a3b8; padding: 8px 0;">Aucune opération de financement enregistrée</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Signatures officielles --}}
        <div class="signatures-section">
            <div class="signature-box">
                <div class="signature-title">Le Responsable des Opérations &amp; Coaching</div>
                <div class="signature-footer">
                    Nom &amp; Date : _______________________________
                </div>
            </div>

            <div class="signature-box">
                <div class="signature-title">La Direction Générale de la SAE (Visa &amp; Cachet)</div>
                <div class="signature-footer">
                    Pour valoir ce que de droit &middot; {{ $tenant->name }}
                </div>
            </div>
        </div>

        {{-- Pied de page --}}
        <footer class="report-footer">
            <p>
                Rapport stratégique édité via <strong>Hubs Africa V2</strong> pour la structure <strong>{{ $tenant->name }}</strong>.<br>
                Données d'impact certifiées conformes aux référentiels Afric'innov &amp; SAEI CUBE.
            </p>
        </footer>
    </div>
</body>
</html>
