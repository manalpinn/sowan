<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Log Kedatangan - Sowan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1a1a2e; }
        h1 { font-size: 18px; font-weight: 700; color: #6d28d9; margin-bottom: 4px; }
        .subtitle { font-size: 11px; color: #666; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        thead tr { background: #6d28d9; color: white; }
        th { padding: 8px 10px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; }
        td { padding: 7px 10px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) td { background: #f9f5ff; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 10px; font-weight: 600; }
        .badge-in { background: #d1fae5; color: #065f46; }
        .badge-out { background: #fce7f3; color: #9d174d; }
        .token { font-family: monospace; font-weight: 700; letter-spacing: 2px; color: #6d28d9; }
        footer { margin-top: 20px; font-size: 10px; color: #999; text-align: center; }
    </style>
</head>
<body>
    <h1>Log Kedatangan</h1>
    <p class="subtitle">
        Dicetak: {{ $generated_at }}
        @if($date_filter) &nbsp;|&nbsp; Filter Tanggal: {{ \Carbon\Carbon::parse($date_filter)->format('d M Y') }} @endif
        &nbsp;|&nbsp; Total: {{ count($checkins) }} data
    </p>

    <table>
        <thead>
            <tr>
                <th style="width:30px">No</th>
                <th>Nama Tamu</th>
                <th>Tipe</th>
                <th>Token</th>
                <th>Event</th>
                <th>Waktu Check-in</th>
                <th>Waktu Check-out</th>
                <th>Status</th>
                <th>Metode</th>
            </tr>
        </thead>
        <tbody>
            @foreach($checkins as $i => $c)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $c['guest_name'] }}</td>
                <td>{{ $c['guest_type'] }}</td>
                <td class="token">{{ $c['guest_token'] }}</td>
                <td>{{ $c['event_name'] }}</td>
                <td>{{ $c['checkin_time'] }}</td>
                <td>{{ $c['checkout_time'] }}</td>
                <td>
                    <span class="badge {{ $c['status'] === 'checkout' ? 'badge-out' : 'badge-in' }}">
                        {{ $c['status'] === 'checkout' ? 'Keluar' : 'Masuk' }}
                    </span>
                </td>
                <td>{{ $c['method'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <footer>Sowan – Sistem Manajemen Tamu &nbsp;|&nbsp; Halaman {PAGE_NUM} dari {PAGE_COUNT}</footer>
</body>
</html>
