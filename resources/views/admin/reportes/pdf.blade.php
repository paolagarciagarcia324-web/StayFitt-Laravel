<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }} · FigueFit</title>
    <style>
        body { font-family: Montserrat, Arial, sans-serif; color: #111; margin: 24px; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        .meta { color: #555; font-size: 13px; margin-bottom: 18px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f3f3f3; }
        .foot { margin-top: 16px; font-size: 11px; color: #777; }
        @media print {
            .no-print { display: none; }
            body { margin: 12px; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:16px">
        <button onclick="window.print()">Imprimir / Guardar PDF</button>
    </div>
    <h1>{{ $titulo }}</h1>
    <p class="meta">Periodo: {{ $periodo }} · Generado: {{ $generado_en }} · Registros: {{ $count }}</p>
    <table>
        <thead>
            <tr>
                @foreach ($headers as $h)
                    <th>{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    @foreach ($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
    <p class="foot">FigueFit / StayFit · Reporte administrativo confidencial</p>
</body>
</html>
