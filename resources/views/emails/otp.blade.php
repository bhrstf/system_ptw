<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Arial', sans-serif; color: #333; line-height: 1.6; }
        .container { padding: 30px; border: 1px solid #e2e8f0; border-radius: 12px; max-width: 600px; margin: 0 auto; }
        .otp-box { font-size: 32px; font-weight: bold; color: #003380; letter-spacing: 6px; margin: 25px 0; padding: 15px; background: #f8fafc; display: inline-block; border-radius: 10px; border: 1px dashed #cbd5e1; }
        .footer { font-size: 11px; color: #94a3b8; margin-top: 30px; border-top: 1px solid #f1f5f9; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <p>Yth. Pengguna PTW System,</p>
        
        <p>Kami menerima permintaan kode verifikasi keamanan (OTP) untuk akun Anda. Silakan gunakan kode berikut untuk melanjutkan:</p>
        
        <center>
            <div class="otp-box">{{ $otp }}</div>
        </center>
        
        <p>Kode ini berlaku selama <strong>2 menit</strong>. Demi keamanan prosedur kerja, jangan berikan kode ini kepada siapa pun.</p>
        
        <p>Jika Anda tidak merasa meminta kode ini, mohon segera hubungi HSE Departemen Batamindo untuk memastikan akun Anda tetap terlindungi.</p>
        
        <p>Salam profesional,<br>
        HSE Departemen Official<br>
        PT Batamindo Investment Cakrawala</p>

        <div class="footer">
            Ini adalah pesan otomatis dari sistem. Mohon tidak membalas email ini secara langsung.
        </div>
    </div>
</body>
</html>