# Todo

## Enhancement: Transaksi sebagai Pengajuan (Requisition)

Transaksi `in` / `adjustment` diubah jadi flow pengajuan:

- [ ] Tambah field `requester_name` di form & database (stock_transactions)
- [ ] Tampilkan `unit_price` di form transaksi & DataTable
- [ ] Tambah kolom `price` di consumable_items (harga satuan)
- [ ] Hitung total nilai stok: `current_stock × unit_price`
- [ ] PDF: Bukti Permintaan (requester → approver)
- [ ] PDF: Bukti Realisasi (setelah approve)
- [ ] Relasi transaksi (pengajuan) → realisasi stok
