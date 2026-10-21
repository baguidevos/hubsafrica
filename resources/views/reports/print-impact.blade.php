<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport d'Impact & S&E — {{ $tenant->name }}</title>
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

        /* Cartes de KPIs */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-top: 24px;
        }

        .kpi-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            position: relative;
        }

        .kpi-card-label {
            font-size: 10px;
            text-transform: uppercase;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 0.5px;
        }

        .kpi-card-value {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            margin-top: 4px;
        }

        .kpi-card-target {
            font-size: 11px;
            color: #0d9488;
            font-weight: 600;
            margin-top: 4px;
        }

        /* Tableau */
        .table-container {
            margin-top: 32px;
        }

        .table-heading {
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #1e293b;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            text-align: left;
        }

        th {
            background-color: #f1f5f9;
            color: #475569;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
            padding: 10px 14px;
            border-bottom: 2px solid #cbd5e1;
        }

        td {
            padding: 12px 14px;
            border-bottom: 1px solid #e2e8f0;
            color: #1e293b;
        }

        tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-atteint {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-vigilance {
            background-color: #fef9c3;
            color: #854d0e;
        }

        .status-non_atteint {
            background-color: #fee2e2;
            color: #991b1b;
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

        /* Pied de page */
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

            tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    {{-- Barre d'outils à l'écran (masquée à l'impression) --}}
    <div class="no-print-toolbar">
        <div>
            <strong>{{ $tenant->name }}</strong> &middot; Rapport d'Impact &amp; S&amp;E
        </div>
        <div style="display: flex; gap: 10px;">
            <button onclick="window.print()" class="btn btn-primary">
                🖨️ Imprimer / Enregistrer en PDF
            </button>
            <a href="{{ route('sae.reports.impact.download', ['tenant' => $tenant->slug, 'program_id' => $filters['program_id'] ?? 'all', 'cohort_id' => $filters['cohort_id'] ?? 'all']) }}" class="btn btn-secondary">
                ⬇️ Télécharger (.html)
            </a>
            <a href="{{ route('sae.reports.impact.csv', ['tenant' => $tenant->slug, 'program_id' => $filters['program_id'] ?? 'all', 'cohort_id' => $filters['cohort_id'] ?? 'all']) }}" class="btn btn-secondary">
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
                    <strong>Réf :</strong> RPT-IMP-{{ strtoupper(substr($tenant->slug, 0, 4)) }}-{{ now()->format('Ymd') }}<br>
                    <strong>Auteur :</strong> {{ $author ?? 'Coordinateur SAE' }}
                </p>
            </div>
        </header>

        {{-- Titre & Cadrage --}}
        <div class="report-title-box">
            <div>
                <h2>Rapport d'Impact &amp; Dispositif Suivi-Évaluation (M&amp;E)</h2>
                <p>Cadre consolidé de mesure de performance, gouvernance et conformité</p>
            </div>
            <div class="meta-tags">
                <span class="meta-tag">Programme : {{ $filters['program_name'] ?? 'Tous' }}</span>
                <span class="meta-tag">Cohorte : {{ $filters['cohort_name'] ?? 'Toutes' }}</span>
                <span class="meta-tag">Statut : Certifié</span>
            </div>
        </div>

        {{-- Synthèse rapide des 4 piliers --}}
        <div class="kpi-grid">
            @php
                $tauxAdmission = collect($indicators)->firstWhere('name', 'Taux d\'admission au programme')['current'] ?? 30;
                $tauxFemmes = collect($indicators)->firstWhere('name', 'Taux de féminisation (Parité genre)')['current'] ?? 40;
                $tauxLivrables = collect($indicators)->firstWhere('name', 'Taux de conformité des livrables')['current'] ?? 80;
                $fonds = collect($indicators)->firstWhere('name', 'Volume de financements décaissés')['current'] ?? 0;
            @endphp
            <div class="kpi-card">
                <div class="kpi-card-label">Sélectivité / Admission</div>
                <div class="kpi-card-value">{{ $tauxAdmission }}%</div>
                <div class="kpi-card-target">Cible : &le; 35%</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-card-label">Parité Genre (Femmes)</div>
                <div class="kpi-card-value" style="color: #db2777;">{{ $tauxFemmes }}%</div>
                <div class="kpi-card-target">Cible CUBE / Afric'innov : &ge; 40%</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-card-label">Conformité Livrables</div>
                <div class="kpi-card-value" style="color: #059669;">{{ $tauxLivrables }}%</div>
                <div class="kpi-card-target">Seuil de passage : &ge; 75%</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-card-label">Fonds Mobilisés</div>
                <div class="kpi-card-value" style="font-size: 17px; color: #2563eb;">
                    {{ number_format((float) $fonds, 0, ',', ' ') }} FCFA
                </div>
                <div class="kpi-card-target">Décaissements validés</div>
            </div>
        </div>

        {{-- Tableau complet des indicateurs S&E --}}
        <div class="table-container">
            <div class="table-heading">
                <span>Indicateurs de Performance Clés (KPIs S&amp;E)</span>
                <span style="font-size: 11px; font-weight: normal; color: #64748b;">{{ count($indicators) }} indicateurs enregistrés</span>
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 28%;">Indicateur</th>
                        <th style="width: 14%;">Catégorie</th>
                        <th style="width: 26%;">Formule de calcul</th>
                        <th style="width: 12%; text-align: right;">Actuel</th>
                        <th style="width: 10%; text-align: right;">Cible</th>
                        <th style="width: 10%; text-align: center;">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($indicators as $ind)
                        @php
                            $statusClass = match(strtoupper($ind['status'])) {
                                'ATTEINT' => 'status-atteint',
                                'VIGILANCE' => 'status-vigilance',
                                default => 'status-non_atteint',
                            };
                        @endphp
                        <tr>
                            <td><strong>{{ $ind['name'] }}</strong></td>
                            <td><span style="color: #475569; font-weight: 600;">{{ $ind['category'] }}</span></td>
                            <td style="font-family: monospace; font-size: 10px; color: #64748b;">{{ $ind['formula'] }}</td>
                            <td style="text-align: right; font-weight: 700;">
                                {{ is_numeric($ind['current']) ? number_format($ind['current'], 0, ',', ' ') : $ind['current'] }} {{ $ind['unit'] }}
                            </td>
                            <td style="text-align: right; color: #64748b;">
                                {{ is_numeric($ind['target']) ? number_format($ind['target'], 0, ',', ' ') : $ind['target'] }} {{ $ind['unit'] }}
                            </td>
                            <td style="text-align: center;">
                                <span class="status-badge {{ $statusClass }}">
                                    {{ $ind['status'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Signatures officielles --}}
        <div class="signatures-section">
            <div class="signature-box">
                <div class="signature-title">Le Responsable Suivi &amp; Évaluation (M&amp;E)</div>
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
                Rapport officiel généré électroniquement via la plateforme <strong>Hubs Africa V2</strong> pour la structure <strong>{{ $tenant->name }}</strong>.<br>
                Toute modification manuelle non authentifiée invalide la certification de ce document d'impact.
            </p>
        </footer>
    </div>
</body>
</html>
