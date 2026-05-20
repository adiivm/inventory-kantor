<table class="table table-sm table-hover border" style="font-size: 12px;">
    <thead class="table-warning text-dark">
        <tr>
            <th>Tanggal</th>
            <th>Auditor</th>
            <th>Catatan & Foto</th>
        </tr>
    </thead>
    <tbody>
        @forelse($auditLogs as $audit)
        <tr>
            <td>{{ \Carbon\Carbon::parse($audit->audit_date)->format('d M Y') }}</td>
            <td class="fw-bold">{{ $audit->auditor_name ?? 'Anonim' }}</td>
            <td>
                <div>{{ $audit->notes ?? 'Tidak ada catatan' }}</div>
                @if($audit->image_path)
                <div class="mt-1">
                    @if(file_exists(public_path('storage/audit/' . $audit->image_path)))
                    <img src="{{ asset('/storage/audit/' . $audit->image_path) }}" 
                         class="img-thumbnail" 
                         style="max-height: 80px; cursor: pointer;"
                         onclick="window.open('{{ asset('/storage/audit/' . $audit->image_path) }}', '_blank')">
                    @else
                    <span class="badge bg-warning">Foto tidak ditemukan</span>
                    @endif
                </div>
                @endif
            </td>
        </tr>
        @empty
        <tr><td colspan="3" class="text-center">Belum pernah diaudit fisik (QR Scan).</td></tr>
        @endforelse
    </tbody>
</table>