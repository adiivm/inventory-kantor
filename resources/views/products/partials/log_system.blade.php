<table class="table table-sm table-striped border" style="font-size: 12px;">
    <thead class="table-secondary">
        <tr>
            <th>Waktu</th>
            <th>User</th>
            <th>Aksi</th>
            <th>Detail Perubahan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($systemLogs as $log)
        <tr>
            <td>{{ $log->created_at->format('d/m/y H:i') }}</td>
            <td>{{ $log->user_name }}</td>
            <td><span class="badge bg-primary">{{ $log->action }}</span></td>
            <td>{{ $log->description }}</td>
        </tr>
        @empty
        <tr><td colspan="4" class="text-center">Tidak ada history sistem.</td></tr>
        @endforelse
    </tbody>
</table>