<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivasi Akun PinjamIn</title>
</head>
<body style="margin:0;padding:0;background:#f4fbff;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:linear-gradient(180deg,#f4fbff 0%,#ffffff 100%);padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:640px;background:#ffffff;border:1px solid #dbeafe;border-radius:24px;overflow:hidden;box-shadow:0 18px 50px rgba(15,23,42,0.08);">
                    <tr>
                        <td style="padding:0;">
                            <div style="background:linear-gradient(135deg,#20a7db 0%,#007dff 100%);padding:32px 36px;color:#ffffff;">
                                <div style="font-size:14px;letter-spacing:.14em;text-transform:uppercase;opacity:.9;">PinjamIn</div>
                                <div style="font-size:28px;line-height:1.2;font-weight:700;margin-top:10px;">Aktivasi akun kamu</div>
                                <div style="font-size:15px;line-height:1.7;margin-top:12px;opacity:.95;">Akun untuk <strong>{{ $user->name }}</strong> sudah dibuat oleh admin. Klik tombol di bawah untuk membuat password baru dan mengaktifkan akun.</div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:36px;">
                            <p style="margin:0 0 18px;font-size:16px;line-height:1.8;">Halo, <strong>{{ $user->name }}</strong>.</p>
                            <p style="margin:0 0 24px;font-size:15px;line-height:1.8;color:#334155;">Untuk melanjutkan, silakan buat password baru melalui tombol berikut. Setelah password diset, kamu bisa login ke aplikasi menggunakan email ini.</p>

                            <div style="margin:32px 0;text-align:center;">
                                <a href="{{ $resetUrl }}" style="display:inline-block;background:linear-gradient(135deg,#20a7db 0%,#007dff 100%);color:#ffffff;text-decoration:none;font-weight:700;font-size:15px;line-height:1;border-radius:999px;padding:16px 28px;box-shadow:0 12px 24px rgba(0,125,255,0.22);">Buat Password Sekarang</a>
                            </div>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:28px 0 0;background:#f8fbff;border:1px solid #dbeafe;border-radius:18px;">
                                <tr>
                                    <td style="padding:18px 20px;">
                                        <div style="font-size:13px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#007dff;margin-bottom:8px;">Catatan</div>
                                        <div style="font-size:14px;line-height:1.8;color:#334155;">Tautan ini akan membawa kamu ke halaman reset password bawaan aplikasi. Jika kamu tidak merasa membuat akun ini, abaikan email ini.</div>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:28px 0 0;font-size:14px;line-height:1.8;color:#64748b;">Terima kasih,<br>{{ config('app.name') }}</p>
                            <p style="margin:16px 0 0;font-size:12px;line-height:1.8;color:#94a3b8;word-break:break-all;">Jika tombol tidak bekerja, salin dan buka tautan ini: <br>{{ $resetUrl }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
