<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Undangan - {{ $guest->name }}</title>
    <style>
        @page { margin: 0; padding: 0; size: a4 portrait; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            height: 100%;
            width: 100%;
            margin: 0;
            padding: 0;
            background: #ffffff;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #1e293b;
        }
        .full-page-table {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
            margin: 0;
            padding: 0;
        }
        .page-cell {
            vertical-align: middle;
            text-align: center;
            padding: 40px;
        }
        .container {
            width: 480px;
            margin: 0 auto;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            overflow: hidden;
            background: #ffffff;
            text-align: left;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        /* Template Themes */
        .header {
            color: #ffffff;
            padding: 25px 20px;
            text-align: center;
        }
        /* Formal Theme (Default) */
        .theme-formal .header { background: #4f46e5; }
        .theme-formal .guest-name { color: #4f46e5; }
        .theme-formal .token-value { color: #4f46e5; }

        /* Wedding Theme */
        .theme-wedding .header { background: #f472b6; }
        .theme-wedding .guest-name { color: #db2777; font-family: 'Georgia', serif; }
        .theme-wedding .token-value { color: #db2777; }
        .theme-wedding .logo-box { border-radius: 50%; width: 50px; height: 50px; line-height: 32px; }

        /* Corporate Theme */
        .theme-corporate .header { background: #0f172a; }
        .theme-corporate .guest-name { color: #0f172a; }
        .theme-corporate .token-value { color: #0f172a; }
        .theme-corporate .logo-box { border-radius: 4px; }

        .logo-box {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 10px;
            margin-bottom: 12px;
        }
        .logo-text { font-size: 22px; font-weight: 900; letter-spacing: -1px; }
        .event-name { font-size: 26px; font-weight: 800; margin-bottom: 2px; line-height: 1.2; }
        .official-tag { font-size: 11px; text-transform: uppercase; letter-spacing: 2px; opacity: 0.8; font-weight: 700; }
        .body-content { padding: 30px 40px; text-align: center; }
        .salutation { font-size: 14px; color: #64748b; margin-bottom: 5px; }
        .guest-name { font-size: 24px; font-weight: 800; margin-bottom: 5px; }
        .guest-type { display: inline-block; background: #f1f5f9; color: #64748b; padding: 4px 14px; border-radius: 999px; font-size: 12px; font-weight: 700; margin-bottom: 20px; }
        .event-details { background: #f8fafc; border-radius: 16px; padding: 18px; margin-bottom: 25px; text-align: left; }
        .detail-item { margin-bottom: 12px; }
        .detail-item:last-child { margin-bottom: 0; }
        .detail-label { font-size: 10px; color: #94a3b8; text-transform: uppercase; font-weight: 700; margin-bottom: 2px; }
        .detail-value { font-size: 15px; font-weight: 700; color: #1e293b; }
        .qr-box { background: #ffffff; border: 2px solid #f1f5f9; padding: 15px; display: inline-block; border-radius: 20px; margin-bottom: 15px; }
        .qr-image { width: 150px; height: 150px; }
        .token-label { font-size: 10px; color: #94a3b8; font-weight: 700; margin-bottom: 5px; }
        .token-value { font-size: 30px; font-weight: 900; letter-spacing: 8px; font-family: monospace; }
        .footer { padding: 20px; text-align: center; border-top: 1px solid #f1f5f9; font-size: 11px; color: #94a3b8; }
        .footer p { margin-bottom: 3px; }
    </style>
</head>
<body class="theme-{{ $event->invitation_template ?? 'formal' }}">
    <table class="full-page-table">
        <tr>
            <td class="page-cell">
                <div class="container">
                    <!-- Header -->
                    <div class="header">
                        <div class="logo-box">
                            <span class="logo-text">SOWAN</span>
                        </div>
                        <h1 class="event-name">{{ $event->name }}</h1>
                        <p class="official-tag">Undangan Resmi Kehadiran</p>
                    </div>

                    <!-- Body -->
                    <div class="body-content">
                        <p class="salutation">Kepada Yang Terhormat,</p>
                        <h2 class="guest-name">{{ $guest->name }}</h2>
                        <div class="guest-type">{{ $guest->type }}</div>

                        <div class="event-details">
                            <div class="detail-item">
                                <p class="detail-label">Tanggal & Waktu</p>
                                <p class="detail-value">{{ \Carbon\Carbon::parse($event->start_date)->translatedFormat('l, d F Y') }}</p>
                            </div>
                            <div class="detail-item">
                                <p class="detail-label">Lokasi Acara</p>
                                <p class="detail-value">{{ $event->location }}</p>
                            </div>
                        </div>

                        <div class="qr-section">
                            <div class="qr-box">
                                <img src="{{ $qrCode }}" class="qr-image" alt="QR Code">
                            </div>
                            <div class="token-container">
                                <p class="token-label">KODE TOKEN MANUAL</p>
                                <p class="token-value">{{ $guest->qr_code }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="footer">
                        <p>Tunjukkan QR Code ini kepada petugas saat memasuki area acara.</p>
                        <p>&copy; 2026 Sowan Management System.</p>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
