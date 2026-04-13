<style>
    @page { size: a4 landscape; margin: 10px; }
    body { font-family: sans-serif; font-size: 8px; }
    table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    th, td { border: 1px solid #000; padding: 3px; word-wrap: break-word; }
    th { background-color: #eee; }
</style>

<h3>LAPORAN INVENTARIS LENGKAP</h3>
<table>
    <thead>
        <tr>
            <th style="width: 10%">SKU</th>
            <th style="width: 15%">Nama</th>
            <th>Kat</th>
            <th>Div</th>
            <th>Ready</th>
            <th>Repair</th>
            <th>Broken</th>
            <th>Price</th>
            <th>User</th>
            <th>Location</th>
            <th>Audit</th>
            <th>Purchase</th>
        </tr>
    </thead>
    <tbody>
        @foreach($barang as $b)
        <tr>
            <td>{{ $b->sku }}</td>
            <td>{{ $b->name }}</td>
            <td>{{ $b->category->name ?? '-' }}</td>
            <td>{{ $b->division->name ?? '-' }}</td>
            <td>{{ $b->stock_ready }}</td>
            <td>{{ $b->stock_repair }}</td>
            <td>{{ $b->stock_broken }}</td>
            <td>{{ number_format($b->price, 0, ',', '.') }}</td>
            <td>{{ $b->held_by ?? '-' }}</td>
            <td>{{ $b->location ?? '-' }}</td>
            <td>{{ $b->last_audited_at }}</td>
            <td>{{ $b->purchase_date }}</td>
        </tr>
        @endforeach
    </tbody>
</table>