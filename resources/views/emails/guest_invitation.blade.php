<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Undangan: {{ $event->name }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        
        <?php 
            $themeColor = $event->theme_color ?? '#7C3AED'; 
            $hex = str_replace('#', '', $themeColor);
            if (strlen($hex) == 6) {
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
                $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
                $contrastColor = ($yiq >= 128) ? '#1e293b' : '#ffffff';
            } else {
                $contrastColor = '#ffffff';
            }
        ?>

        body {
            font-family: 'Inter', Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #1E1B4B; /* text-primary Sowan */
            background-color: #F8F7FF; /* bg-base Sowan */
            margin: 0;
            padding: 40px 20px;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #FFFFFF; /* card-bg Sowan */
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08), 0 2px 4px rgba(0, 0, 0, 0.05); /* shadow-md Sowan */
            border: 1px solid #E8E5F4; /* border Sowan */
        }
        .header {
            background-color: {{ $themeColor }}; /* primary Sowan */
            color: {{ $contrastColor }};
            text-align: center;
            padding: 40px 20px;
            border-bottom: 4px solid rgba(0, 0, 0, 0.15); /* primary-dark Sowan */
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 10px 0 0;
            font-size: 14px;
            color: {{ $contrastColor }}; /* primary-soft Sowan */
            opacity: 0.8;
            font-weight: 500;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 700;
            color: #1E1B4B;
            margin-top: 0;
            margin-bottom: 20px;
        }
        .event-details {
            background-color: #F8F7FF;
            border-radius: 12px;
            padding: 24px;
            margin: 30px 0;
            border: 1px solid #E8E5F4;
        }
        .detail-row {
            margin-bottom: 12px;
        }
        .detail-row:last-child {
            margin-bottom: 0;
        }
        .detail-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #8B87B0; /* text-muted Sowan */
            font-weight: 700;
            display: block;
            margin-bottom: 4px;
        }
        .detail-value {
            font-size: 15px;
            color: #1E1B4B;
            font-weight: 600;
        }
        .quote {
            text-align: center;
            font-style: italic;
            color: #4C4885; /* text-secondary Sowan */
            font-size: 15px;
            margin: 30px 0;
            padding: 0 20px;
        }
        .action-container {
            text-align: center;
            margin: 40px 0 20px;
        }
        .button {
            display: inline-block;
            padding: 14px 32px;
            background-color: {{ $themeColor }};
            color: {{ $contrastColor }};
            text-decoration: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 15px;
            transition: background-color 0.2s;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }
        .fallback-link {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #8B87B0;
            word-break: break-all;
        }
        .fallback-link a {
            color: {{ $themeColor }};
            text-decoration: underline;
        }
        .footer {
            background-color: #F8F7FF;
            text-align: center;
            padding: 24px;
            font-size: 12px;
            color: #8B87B0;
            border-top: 1px solid #E8E5F4;
        }
        
        @media only screen and (max-width: 600px) {
            body { padding: 20px 10px; }
            .content { padding: 30px 20px; }
            .header { padding: 30px 20px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $event->name }}</h1>
            <p>Undangan Resmi</p>
        </div>

        <div class="content">
            <h2 class="greeting">Halo {{ $guest->name }},</h2>
            
            <p style="margin-bottom: 0; color: #4C4885;">Anda diundang untuk menghadiri acara istimewa kami. Kehadiran Anda akan menjadi kehormatan bagi kami.</p>
            
            <div class="event-details">
                <div class="detail-row">
                    <span class="detail-label">Tanggal Acara</span>
                    <span class="detail-value">{{ $event->start_date ? $event->start_date->translatedFormat('l, d F Y') : '-' }}</span>
                </div>
                
                @if($event->start_time && $event->end_time)
                <div class="detail-row" style="margin-top: 10px;">
                    <span class="detail-label">Waktu</span>
                    <span class="detail-value">Pukul {{ \Carbon\Carbon::parse($event->start_time)->format('H.i') }} - {{ \Carbon\Carbon::parse($event->end_time)->format('H.i') }} WIB</span>
                </div>
                @endif
                
                <div class="detail-row" style="margin-top: 20px;">
                    <span class="detail-label">Lokasi</span>
                    <span class="detail-value">{{ $event->location }}</span>
                </div>
                
                @if(!empty($event->google_maps_link))
                <div class="detail-row" style="margin-top: 12px;">
                    <a href="{{ $event->google_maps_link }}" style="color: {{ $themeColor }}; text-decoration: none; font-size: 14px; font-weight: 600;">Buka di Google Maps &rarr;</a>
                </div>
                @endif
            </div>

            @if($event->welcome_message)
                <div class="quote">
                    "{{ $event->welcome_message }}"
                </div>
            @endif

            <div class="action-container">
                <p style="font-size: 14px; color: #4C4885; margin-bottom: 16px;">Klik tombol di bawah untuk melihat detail undangan dan barcode kehadiran Anda.</p>
                <a href="{{ $linkUnik }}" class="button" style="color: #ffffff;">Buka Undangan Web</a>
            </div>
            
            <div class="fallback-link">
                Jika tombol tidak berfungsi, salin tautan berikut:<br>
                <a href="{{ $linkUnik }}">{{ $linkUnik }}</a>
            </div>
        </div>

        <div class="footer">
            Pesan ini dikirim secara otomatis oleh <strong>Sowan Guest Book System</strong>.<br>
            Mohon tidak membalas email ini.
        </div>
    </div>
</body>
</html>
