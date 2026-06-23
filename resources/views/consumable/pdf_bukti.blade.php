<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Bukti Serah Terima - {{ $header->reference_number }}</title>
</head>
<body style="font-family:'DejaVu Sans',sans-serif; font-size:11pt; color:#333; margin:30px 40px;">

    <table style="width:100%; border-collapse:collapse; margin-bottom:20px;">
        <tr>
            <td style="width:70px; text-align:center; vertical-align:middle;">
                @isset($qrCode)
                <img src="{{ public_path('images/ivans_motor.png') }}" style="max-width:60px;" alt="Logo">
                @else
                <img src="/images/ivans_motor.png" style="max-width:60px;" alt="Logo">
                @endisset
            </td>
            <td style="text-align:center; vertical-align:middle;">
                <div style="font-size:18pt; font-weight:bold; margin:0 0 4px 0; color:#1a1a2e;">INVENTORY SYSTEM</div>
                <div style="font-size:14pt; margin:0; font-weight:normal; color:#555;">BUKTI SERAH TERIMA BARANG</div>
                <div style="font-size:10pt; color:#777; margin-top:4px;">No. {{ $header->reference_number }}</div>
            </td>
            @isset($qrCode)
            <td style="width:100px; text-align:right; vertical-align:middle;">
                <img src="data:image/svg+xml;base64,{{ $qrCode }}" style="width:80px; height:80px;" alt="QR">
            </td>
            @endisset
        </tr>
    </table>

    <hr style="border:none; border-top:2px solid #1a1a2e; margin:15px 0;">

    <table style="width:100%; border-collapse:collapse; margin-bottom:20px; font-size:11pt;">
        <tr><td style="padding:4px 8px; vertical-align:top; font-weight:bold; color:#555; width:130px;">Ref Number</td><td style="padding:4px 8px; vertical-align:top; font-weight:bold; color:#1a1a2e;">: {{ $header->reference_number }}</td></tr>
        <tr><td style="padding:4px 8px; vertical-align:top; font-weight:bold; color:#555;">Tanggal</td><td style="padding:4px 8px; vertical-align:top;">: {{ $header->created_at ? $header->created_at->format('d/m/Y') : '-' }}</td></tr>
        <tr><td style="padding:4px 8px; vertical-align:top; font-weight:bold; color:#555;">Peminta</td><td style="padding:4px 8px; vertical-align:top;">: {{ $header->requester_name }}</td></tr>
        <tr><td style="padding:4px 8px; vertical-align:top; font-weight:bold; color:#555;">Divisi</td><td style="padding:4px 8px; vertical-align:top;">: {{ $header->division?->name ?? '-' }}</td></tr>
        @if($header->approved_at)
        <tr><td style="padding:4px 8px; vertical-align:top; font-weight:bold; color:#555;">Disetujui</td><td style="padding:4px 8px; vertical-align:top;">: {{ $header->approved_at->format('d/m/Y H:i') }}</td></tr>
        @endif
        @if($header->received_at)
        <tr><td style="padding:4px 8px; vertical-align:top; font-weight:bold; color:#555;">Diterima</td><td style="padding:4px 8px; vertical-align:top;">: {{ $header->received_at->format('d/m/Y H:i') }}</td></tr>
        @endif
    </table>

    <table style="width:100%; border-collapse:collapse; margin-bottom:25px;">
        <thead>
            <tr>
                <th style="background:#1a1a2e; color:#fff; padding:8px 10px; text-align:center; width:40px; font-size:10pt;">No</th>
                <th style="background:#1a1a2e; color:#fff; padding:8px 10px; text-align:left; font-size:10pt;">Nama Barang</th>
                <th style="background:#1a1a2e; color:#fff; padding:8px 10px; text-align:center; width:80px; font-size:10pt;">Qty</th>
                <th style="background:#1a1a2e; color:#fff; padding:8px 10px; text-align:center; width:80px; font-size:10pt;">Satuan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($header->details as $index => $detail)
            <tr>
                <td style="padding:6px 10px; border:1px solid #ddd; text-align:center;">{{ $index + 1 }}</td>
                <td style="padding:6px 10px; border:1px solid #ddd;">{{ $detail->consumableItem->name ?? 'Unknown' }}</td>
                <td style="padding:6px 10px; border:1px solid #ddd; text-align:center;">{{ $detail->qty }}</td>
                <td style="padding:6px 10px; border:1px solid #ddd; text-align:center;">{{ $detail->consumableItem->unit ?? '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="4" style="padding:6px 10px; border:1px solid #ddd; text-align:center; color:#999;">Tidak ada barang</td></tr>
            @endforelse
        </tbody>
    </table>

    <table style="width:100%; border-collapse:collapse; margin-top:30px;">
        <tr>
            <td style="width:50%; text-align:center; vertical-align:top; padding:10px 20px;">
                <div style="font-weight:bold; font-size:11pt; margin-bottom:8px;">Admin / Purchasing</div>
                @if($header->admin_signature)
                    <img src="{{ $header->admin_signature }}" style="max-width:150px; max-height:60px; margin:5px 0;" alt="TTD Admin">
                @else
                    <div style="margin:20px 0; color:#ccc; font-style:italic;">(Belum ditandatangani)</div>
                @endif
                <div style="border-top:1px solid #333; width:180px; margin:35px auto 5px auto;"></div>
                <div style="font-weight:bold; font-size:10pt;">{{ $header->approver->name ?? '___________________' }}</div>
                <div style="font-size:9pt; color:#777;">Admin / Purchasing</div>
                @if($header->received_at)
                    <div style="font-size:9pt; color:#555; margin-top:2px;">{{ $header->received_at->format('d/m/Y H:i') }}</div>
                @endif
            </td>
            <td style="width:50%; text-align:center; vertical-align:top; padding:10px 20px;">
                <div style="font-weight:bold; font-size:11pt; margin-bottom:8px;">Penerima</div>
                @if($header->receiver_signature)
                    <img src="{{ $header->receiver_signature }}" style="max-width:150px; max-height:60px; margin:5px 0;" alt="TTD Penerima">
                @else
                    <div style="margin:20px 0; color:#ccc; font-style:italic;">(Belum ditandatangani)</div>
                @endif
                <div style="border-top:1px solid #333; width:180px; margin:35px auto 5px auto;"></div>
                <div style="font-weight:bold; font-size:10pt;">{{ $header->requester_name }}</div>
                <div style="font-size:9pt; color:#777;">Penerima</div>
                @if($header->received_at)
                    <div style="font-size:9pt; color:#555; margin-top:2px;">{{ $header->received_at->format('d/m/Y H:i') }}</div>
                @endif
            </td>
        </tr>
    </table>

    <div style="margin-top:30px; text-align:center; font-size:9pt; color:#999;">
        Dokumen ini sah dan diproses secara elektronik. &mdash; {{ $header->reference_number }}
    </div>

</body>
</html>