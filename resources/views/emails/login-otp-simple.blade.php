<!DOCTYPE html>
<html>
<head>
    <title>Kode OTP Login - Sowan</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px;">
    <h2 style="color: #4f46e5; text-align: center; font-weight: 900; letter-spacing: -1px;">SOWAN.</h2>
    <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;">
    <p>Halo,</p>
    <p>Anda menerima email ini karena ada permintaan login ke halaman administrator Sowan.</p>
    <p>Silakan masukkan kode OTP berikut untuk melanjutkan proses login Anda:</p>
    <div style="background-color: #f8fafc; border: 1px dashed #cbd5e1; padding: 15px; text-align: center; margin: 25px 0; border-radius: 6px;">
        <span style="font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #4f46e5;">{{ $otp }}</span>
    </div>
    <p style="font-size: 13px; color: #64748b;">Kode ini hanya berlaku selama <strong>5 menit</strong>. Jika Anda tidak merasa melakukan request ini, abaikan email ini atau segera ganti password Anda.</p>
    <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;">
    <p style="font-size: 11px; color: #94a3b8; text-align: center;">&copy; {{ date('Y') }} Sowan Management System.</p>
</body>
</html>
