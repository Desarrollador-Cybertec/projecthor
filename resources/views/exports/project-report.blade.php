<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte — {{ $project->name }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; margin: 24px; }
        h1 { font-size: 20px; margin: 0 0 2px; }
        h2 { font-size: 14px; margin: 24px 0 8px; border-bottom: 2px solid {{ $project->color }}; padding-bottom: 4px; }
        .muted { color: #64748b; }
        .header { border-bottom: 3px solid {{ $project->color }}; padding-bottom: 12px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th { text-align: left; background: #f1f5f9; padding: 6px 8px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.03em; color: #475569; }
        td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        .badge { display: inline-block; padding: 1px 8px; border-radius: 10px; background: #e2e8f0; font-size: 10px; }
        .meta td { border: none; padding: 3px 8px 3px 0; }
        .meta .label { color: #64748b; width: 140px; }
        .progress { background: #e2e8f0; border-radius: 6px; height: 8px; width: 100%; }
        .progress > div { background: {{ $project->color }}; height: 8px; border-radius: 6px; }
        .footer { margin-top: 32px; font-size: 10px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $project->name }}</h1>
        <p class="muted" style="margin: 0;">Cliente: {{ $project->client_name }} · Estado: {{ $project->status->label() }} · Prioridad: {{ $project->priority->label() }}</p>
    </div>

    <table class="meta">
        <tr><td class="label">Responsable</td><td>{{ $project->responsible?->name }}</td></tr>
        <tr><td class="label">Equipo</td><td>{{ $project->members->pluck('name')->implode(', ') ?: '—' }}</td></tr>
        <tr><td class="label">Fecha inicio</td><td>{{ $project->start_date?->translatedFormat('d \d\e F \d\e Y') ?? '—' }}</td></tr>
        <tr><td class="label">Fecha entrega</td><td>{{ $project->due_date?->translatedFormat('d \d\e F \d\e Y') ?? '—' }}</td></tr>
        <tr><td class="label">Progreso general</td><td>{{ $project->progress }}%</td></tr>
        @if ($project->repository_url)
            <tr><td class="label">Repositorio</td><td>{{ $project->repository_url }}</td></tr>
        @endif
        @if ($project->production_url)
            <tr><td class="label">Producción</td><td>{{ $project->production_url }}</td></tr>
        @endif
    </table>

    @if ($project->description)
        <h2>Descripción</h2>
        <p>{{ $project->description }}</p>
    @endif

    <h2>Etapas</h2>
    <table>
        <thead>
            <tr>
                <th>Etapa</th>
                <th>Estado</th>
                <th>Avance</th>
                <th>Inicio</th>
                <th>Estimada</th>
                <th>Final</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($project->stages as $stage)
                <tr>
                    <td><strong>{{ $stage->name }}</strong></td>
                    <td><span class="badge">{{ $stage->status->label() }}</span></td>
                    <td style="width: 110px;">
                        <div class="progress"><div style="width: {{ $stage->progress }}%;"></div></div>
                        <span class="muted">{{ $stage->progress }}%</span>
                    </td>
                    <td>{{ $stage->starts_on?->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $stage->estimated_end_on?->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $stage->ended_on?->format('d/m/Y') ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Actividades ({{ $project->activities->count() }})</h2>
    <table>
        <thead>
            <tr>
                <th>Actividad</th>
                <th>Etapa</th>
                <th>Responsable</th>
                <th>Prioridad</th>
                <th>Estado</th>
                <th>Finalizada</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($project->activities->sortBy('position') as $activity)
                <tr>
                    <td>{{ $activity->name }}</td>
                    <td>{{ $activity->stage?->name ?? '—' }}</td>
                    <td>{{ $activity->responsible?->name ?? '—' }}</td>
                    <td>{{ $activity->priority->label() }}</td>
                    <td><span class="badge">{{ $activity->status->label() }}</span></td>
                    <td>{{ $activity->completed_at?->format('d/m/Y') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted">Sin actividades registradas.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generado por ProjectFlow el {{ now()->translatedFormat('d \d\e F \d\e Y H:i') }}
    </div>
</body>
</html>
