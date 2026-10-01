<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Reminder Pensiun</title>
</head>

<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;">

        {{-- Header --}}
        <div style="background: #d9534f; color: #fff; padding: 20px;">
            <h2 style="margin: 0; font-size: 18px;">
                ⚠️ Reminder Pensiun & Aset
            </h2>
        </div>

        {{-- Body --}}
        <div style="padding: 25px;">
            <p>Halo <strong>{{ $asset->currentUser->name ?? 'Bapak/Ibu' }}</strong>,</p>

            @if ($type === 'akan_pensiun')
                <p>
                    Masa pensiun Anda akan tiba pada
                    <strong>{{ optional($asset->currentUser->waktu_pensiun)->translatedFormat('d F Y') }}</strong>.
                </p>
                <p>Mohon persiapan pengembalian aset kantor berikut:</p>
            @elseif($type === 'sudah_pensiun')
                <p>Selamat menikmati masa pensiun. 🙏</p>
                <p>Kami catat masih ada aset kantor yang belum dikembalikan:</p>
            @else
                <p>Aset berikut perlu segera dikembalikan:</p>
            @endif

            {{-- Detail Aset --}}
            <table style="width:100%; border-collapse: collapse; margin: 15px 0;">
                <tr>
                    <td style="padding:10px;border:1px solid #ddd;background:#fafafa;width:35%;">
                        <b>Aset</b>
                    </td>
                    <td style="padding:10px;border:1px solid #ddd;">
                        {{ $asset->brand }} {{ $asset->model }}
                    </td>
                </tr>
                <tr>
                    <td style="padding:10px;border:1px solid #ddd;background:#fafafa;">
                        <b>Kode Aset</b>
                    </td>
                    <td style="padding:10px;border:1px solid #ddd;">
                        {{ $asset->asset_code ?? '-' }}
                    </td>
                </tr>
                <tr>
                    <td style="padding:10px;border:1px solid #ddd;background:#fafafa;">
                        <b>Serial Number</b>
                    </td>
                    <td style="padding:10px;border:1px solid #ddd;">
                        {{ $asset->serial_number }}
                    </td>
                </tr>
                <tr>
                    <td style="padding:10px;border:1px solid #ddd;background:#fafafa;">
                        <b>Status</b>
                    </td>
                    <td style="padding:10px;border:1px solid #ddd;">
                        {{ $asset->status_label }}
                    </td>
                </tr>
            </table>

            <p>Silakan koordinasi dengan Tim IT/HRD untuk proses serah terima.</p>

            <p style="margin-top: 25px;">
                <a href="{{ route('siam.assets.show', $asset) }}"
                    style="background:#007bff;color:#fff;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;">
                    Lihat Detail Aset
                </a>
            </p>
        </div>

        {{-- Footer --}}
        <div style="background:#f4f4f4; color:#888; padding:15px; font-size:12px; text-align:center;">
            Email otomatis dari <b>SIAM</b> · {{ now()->translatedFormat('d F Y H:i') }}
        </div>
    </div>
</body>

</html>
