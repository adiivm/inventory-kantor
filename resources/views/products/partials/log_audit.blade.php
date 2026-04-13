<table class="table table-sm table-hover border" style="font-size: 12px;">
    <thead class="table-warning text-dark">
        <tr>
            <th>Tanggal Audit</th>
            <th>Auditor</th>
            <th>Catatan Kondisi Fisik</th>
        </tr>
    </thead>
    <tbody>
        @forelse($auditLogs as $audit)
        <tr>
            <td>{{ \Carbon\Carbon::parse($audit->audit_date)->format('d M Y') }}</td>
            <td class="fw-bold">{{ $audit->auditor_name ?? 'Anonim' }}</td>
            <td>{{ $audit->notes ?? 'Tidak ada catatan' }}</td>
        </tr>
        @empty
        <tr><td colspan="3" class="text-center">Belum pernah diaudit fisik (QR Scan).</td></tr>
        @endforelse
    </tbody>
</table>