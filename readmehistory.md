# Inventory Kantor

Aplikasi inventaris kantor berbasis Laravel dengan Docker.

## Tech Stack

- Laravel 12
- PHP 8.4
- PostgreSQL 15
- Apache
- Bootstrap 5
- Yajra DataTables

## Cara Menjalankan Server

### Start server (setiap pagi)

```bash
cd /home/it-adi/ProyekAssets/inventory-kantor
docker compose up -d
```

### Stop server

```bash
docker compose down
```

### Restart server (jika ada masalah)

```bash
docker compose restart
```

### Melihat logs

```bash
docker compose logs -f
```

### Cek status

```bash
docker ps
```

### Jika database reset (data hilang)

Ini terjadi karena volume PostgreSQL dihapus saat container di-stop. Untuk mencegahnya:

1. Pastikan tidak menjalankan `docker compose down -v` (yang `-v` menghapus volumes)
2. Data akan bertahan selama tidak menghapus container/volume

Untuk backup manual:

```bash
# Export database
docker exec inventory-postgres pg_dump -U inventory_user inventory_kantor > backup.sql

# Import database
docker exec -T inventory-postgres psql -U inventory_user inventory_kantor < backup.sql
```

## Akses

- Web: http://localhost:8080
- Akses dari HP (sejaringan): http://172.17.7.70:8080

## Login

- Email: admin@test.com
- Password: 123456

## Database

- PostgreSQL port: 5433
- Konfigurasi ada di .env

## Image Storage

- Lokasi: /mnt/DATA/imagepublic
- Folder: photos, products, audit

## Cara Menggunakan

### Menu Utama

- **Dashboard** - Halaman utama menampilkan statistik inventaris
- **Produk** - Kelola data produk inventaris
- **Kategori** - Kelola kategori produk
- **Divisi** - Kelola divisi departemen
- **Lokasi** - Kelola lokasi penyimpanan
- **User** - Kelola pengguna sistem
- **Laporan** - Export data ke Excel
- **Audit** - Pencatatan inventaris
- **Profile** - Ubah profil & password

### Fitur Produk

- Tambah produk baru dengan foto
- Edit detail produk
- Hapus (pindah ke trash)
- Restore produk yang dihapus
- Arsip produk
- Tambah/edit hapus foto produk
- Cetak label barcode (PDF)
- **Import Excel** - Import banyak produk dari file Excel
- **Bulk Print Labels** - Cetak QR Code massal untuk banyak produk

### Cara Import Produk dari Excel

1. Klik menu **Produk**
2. Klik tombol **Import Excel** (icon upload)
3. Unduh template terlebih dahulu
4. Isi data sesuai format template
5. Upload file Excel (.xlsx, .xls, .csv)
6. Klik **Import Data**

**Kolom yang didukung:**

- sku, name, category/kategori, division/divisi, held_by/pemegang
- location/lokasi, supplier, condition/kondisi, price/harga
- purchase_date/tanggal_beli, warranty_expiry_date/garansi

### Cara Bulk Print QR Labels

1. Di halaman **Produk**, centang produk yang ingin dicetak
2. Klik tombol **Print Massal** (icon printer)
3. Sistem membuka halaman print dengan QR Code
4. Cetak menggunakan printer thermal 70mm x 35mm

### Cara Filter Garansi

Dashboard memiliki card khusus untuk tracking garansi:

- **Garansi Kritis** - Produk dengan garansi ≤ 30 hari
- **Garansi Expired** - Produk dengan garansi sudah habis

Klik card tersebut untuk melihat daftar produk yang garansinya kritis/expired.

### Cara Input Produk Baru

1. Klik menu **Produk**
2. Klik tombol **+ Tambah**
3. Isi data: Nama, SKU, Kategori, Kondisi, Jumlah, Divisi, Lokasi
4. Upload foto produk (opsional)
5. Klik **Simpan**

### Cara Audit Inventaris

1. Scan QR Code pada label produk menggunakan HP
2. Atau akses langsung: `/audit/direct/{sku}`
3. Sistem menampilkan data produk
4. Isi auditor, kondisi, dan upload foto bukti
5. Klik **Submit Audit**

### Cara Export Laporan

1. Klik menu **Laporan**
2. Filter berdasarkan tanggal/kategori/divisi
3. Klik **Export Excel**

### Soft Delete (Arsip)

- Produk yang dihapus tidak langsung hilang, dipindahkan ke trash
- Klik **Trash** untuk melihat produk yang dihapus
- Produk di trash bisa di-**restore** (kembalikan)
- Klik **Hapus Permanen** untuk menghapus definitif
- Klik **Arsip** untuk menyimpan ke arsip (tidak muncul di list utama)

### Status Produk

Setiap produk memiliki status kondisi:

- **Ready** - barang siap pakai
- **Repair** - sedang diperbaiki/servis
- **Broken** - rusak/tidak bisa digunakan

## Fitur Tambahan

### Dashboard Analytics

- **Total Aset** - Jumlah seluruh produk
- **Kondisi Baik** - Produk dengan status ready
- **Perbaikan/Rusak** - Produk yang butuh perhatian
- **Aset Archive** - Produk yang diarsipkan
- **Garansi Kritis** - Produk garansi ≤ 30 hari
- **Garansi Expired** - Produk garansi sudah habis

### Notifikasi Navbar

- Icon lonceng di navbar menampilkan jumlah garansi kritis
- Klik lonceng untuk melihat detail 5 produk teratas
- Setiap item显示 sisa hari garansi

### Badge Warna SKU

Di tabel produk, SKU menampilkan badge warna berdasarkan garansi:

- **Hijau** - Garansi masih lama (>30 hari)
- **Kuning** - Garansi kritis (≤30 hari)
- **Abu-abu** - Garansi habis atau tidak ada

### Filter URL

Semua filter di dashboard dapat diakses langsung via URL:

- `?condition=ready` - Filter kondisi baik
- `?condition=repair` - Filter repair/broken
- `?warranty_status=critical` - Filter garansi kritis
- `?warranty_status=expired` - Filter garansi expired
- `?search_sku=IVM-xxxxxxx` - Filter SKU tertentu

### Menu Suppliers

Kelola data supplier/pemasok barang:

- Tambah supplier baru
- Edit data supplier
- Hapus supplier (soft delete)
- Saat import Excel, supplier baru otomatis dibuat jika belum ada

### Menu User Management (Admin only)

Kelola pengguna sistem:

- Tambah user baru
- Edit data user (nama, email, role)
- Reset password
- Hapus user

### Role Pengguna

- **Admin** - Akses penuh ke semua fitur termasuk user management
- **Staff** - Hanya bisa lihat, tambah, dan edit produk (tidak bisa hapus user)

### Menu Laporan

Fitur export data untuk reporting:

- Filter berdasarkan: Tanggal, Kategori, Divisi, Kondisi, Lokasi
- Export ke Excel
- Kolom yang diexport bisa dipilih sesuaikebutuhan

### Menu Archive/Trash

- **Trash** - Produk yang dihapus (soft delete), bisa restore
- **Arsip** - Produk yang diarsipkan, tidak muncul di list utama tapi masih tersimpan

### Download Template Import

Sebelum import Excel, bisa download template kosong:

1. Klik **Import Excel**
2. Klik **Unduh Template**
3. Template akan terdownload dengan header kolom yang benar

### Cara Akses dari HP

1. Pastikan HP dan komputer server在同一网络 (sejaringan)
2. Buka browser HP, akses: `http://172.17.7.70:8080`
3. Login seperti biasa
4. Dashboard dan fitur sudah responsif untuk layar HP

## Changelog

### 2026-05-21 — Sesi 1

- **Form Request Validation**: `StoreProductRequest`, `UpdateProductRequest` — validasi dipisah dari controller ke Form Request
- **Eloquent Scopes**: `active()`, `notActive()`, `warrantyCritical()`, `warrantyExpired()`, `condition()`, `stockLow()` — query berulang disederhanakan
- **Model Observer**: `ProductObserver` — logic `saving()` (stock=1, default condition) dipindah dari model
- **PHP 8 Enums**: `ProductCondition`, `ProductStatus`, `UserRole` — type-safe, validasi pakai `Rule::enum()`
- **Policy Improvement**: Inline `role` check diganti `Gate::allows('admin-only')` / `Gate::authorize('admin-only')`
- **Custom Artisan Command**: `inventory:check-warranty` — cek garansi kritis/expired dari CLI
- **Task Scheduling**: `CheckWarrantyJob` dijadwalkan tiap jam 08:00 via `routes/console.php`
- **Cron + Queue Worker**: `cron` + `queue:work` berjalan otomatis di container via `docker-entrypoint.sh`
- **Queue + Notification**: `CheckWarrantyJob` + `WarrantyCriticalAlert` (database channel) — notifikasi garansi muncul di bell icon navbar
- **Migration Notifications**: Tabel `notifications` (UUID, morphs, data JSON, read_at)
- **Rebuild Container**: `docker compose build --no-cache app` + restart container untuk aktifkan cron & queue worker

### 2026-05-21 — Sesi 0 (Sebelum ada changelog)

- **SKU Format**: Semua SKU diubah menjadi 8 digit (`IVM-00000001` s.d. `IVM-00000032`) dan berurutan berdasarkan ID
- **SKU Generate**: Auto-generate SKU di create & import menggunakan format 8 digit
- **Unique Constraints**: Menambahkan UNIQUE constraint ke `categories.name`, `divisions.name`, `suppliers.name`
- **Data Duplikat Dibersihkan**: 5 duplikat kategori & 5 duplikat divisi digabung
- **ProductController@update**:
  - Validasi unique SKU ditambahkan
  - Duplicate code block (update 2x per request) dihapus
  - Optimasi: kompresi gambar otomatis (max 1920px, >500KB dikompres)
  - Batch query relasi (kurangi N+1 query)
- **Export Excel**:
  - Filter: kategori, divisi, lokasi, kondisi, range tanggal beli
  - Pilihan status: Active Only / All / Archive Only
  - Kolom `is_active` (Status) ditambahkan
  - Label header konsisten English
  - `FromCollection` → `FromQuery` + chunk 500 untuk performa
  - Filter `is_active = active` otomatis (kecuali pilih All/Archive)
- **Search**:
  - Search multi-field (sku, name, category, division, held_by, location, supplier, condition)
  - Case-insensitive (`ILIKE`)
  - Filter dropdown di halaman Products (Kategori, Divisi, Pemegang, Lokasi, Kondisi)
  - Filter dropdown di halaman Archive (sama)
  - Archive page: client-side → server-side DataTables
- **SweetAlert2**: Dipindah ke `<head>` + fallback jika CDN gagal
- **Notifikasi Session**: Success/error alert dipindah ke `@push('scripts')` agar tampil
- **Dashboard**: Card "Aset Archive" sekarang menghitung semua non-aktif (`!= 'active'`), bukan hanya `archive`
- **SoftDelete Import**: Hapus `use SoftDeletes` (tidak dipakai)

### Latest

- **Master Data page** (`/master-data`): manage Categories, Divisions, Held By, Locations with admin-only delete + FK protection
- **Price & purchase_date** made nullable (database + Form Requests)
- **Supplier AJAX fix**: `SupplierController@store` now uses `Validator` directly to always return JSON; JS `.then()` handles `data.success === false`
- **Product form alert**: SweetAlert2 warning if Category/Division not selected
- **Product index** ordered by SKU descending (newest first)

## ð Changelog

### 2026-06-18

- **User Management:** Tambah flag `can_approve` pada user (approval distribution).
- **Consumable Stock Report:** Export Excel laporan stok consumable.
- **Notifikasi Bell:** Dua bell terpisah â bell kuning untuk garansi kritis, bell hijau untuk permintaan distribusi pending. Notifikasi otomatis ditandai read saat di-approve/reject.
- **Consumable Import:** Download template Excel untuk import master barang.
- **Consumable Items:** Perbaikan tombol edit & soft delete (class selector mismatch).
- **Consumable Dashboard:** Halaman dashboard consumable professional dengan 3 row layout — KPI cards (Total Barang, Stok Menipis, Distribusi Pending, Transaksi Hari Ini), Action Center (Urgent Restock + Pending Approvals dengan tombol shortcut), Visual Analytics (Line Chart Outflow 7 hari + Bar Chart Top 5 Requested Items).
- **Consumable Kartu Stok:** Fitur riwayat pergerakan stok per item — tombol History (🕒) di master barang, menampilkan timeline transaksi dengan tipe, qty, sisa stok, dan referensi distribusi.
- **Approval Transaksi Masuk:** Migration tambah kolom `status` & `approved_by` di `stock_transactions`. Store set status `pending`, tidak langsung tambah stok. Approve/Reject via tombol di DataTable (hanya user `can_approve`). Notifikasi ke approver via `StockInPendingNotification` muncul di green bell bersama notifikasi distribusi.
- **Sample Data Consumable:** Insert 30 sample items (Alat Tulis, Kertas & Print, Kebersihan, Minuman & Snack, Elektronik) + category/unit/supplier seeding via tinker.

### 2026-06-22

- **Activity Log System:** Table `activity_logs`, model, helper (`App\Helpers\Activity`), controller, view (server-side DataTable, filter module/action/date, detail modal with diff).
- **Activity Log Integration:** `Activity::logCreate/logUpdate/logDelete/log` injected into ConsumableItem, StockTransaction, Distribution, User, Product, Category, Division, Supplier controllers.
- **Sidebar & Route:** "Riwayat Aktivitas" link (admin only) under Master menu.
- **Date Format Fix:** `formatValue()` in activity log detail modal — date-only fields (purchase_date, warranty_expiry_date) no longer show `, 00.00` time (manual parsing + key-based time detection).
- **Role Dropdown Fix:** User edit modal role values changed from `Staff`/`Admin` to `staff`/`admin`.
- **Green Bell Fix:** Notifikasi pending items now queries DB directly instead of relying on `notifications` table.
- **PostgreSQL Fix:** `data->>'distribution_id'` → `data::jsonb->>'distribution_id'` in DistributionController.
- **Dashboard KPI Links:** Total Barang, Stok Menipis, Distribusi Pending cards are now clickable.
- **Remove Unused Card:** "Transaksi Hari Ini" removed from dashboard.
- **Filter UI:** Added filter UI to consumable items, transactions, distributions, and reports pages.
- **Tambah Stok Fix:** Tombol "Tambah Stok" di dashboard urgent restock sekarang auto-filter DataTable, pre-select modal form, dan auto-open modal untuk input stok.
- **Top 5 Requested Items Fix:** Query hanya menghitung distribution dengan status `approved` (sebelumnya semua status termasuk rejected).
- **Adjustment Fix:** Qty penyesuaian sekarang bisa minus (B1). `approve()` pakai `increment`/`decrement` sesuai tanda qty. Validasi: `in` minimal 1, `adjustment` != 0. View ditambah hint untuk input negatif.
- **Notifikasi Adjustment:** Green bell sekarang juga menampilkan pending adjustment (sebelumnya hanya `type=in`). Tampilan badge "Penyesuaian" di bell dropdown. `StockInPendingNotification` pesan dibedakan untuk `in` vs `adjustment`.
- **Dashboard KPI Cards:** Style cards consumable dashboard diselaraskan dengan asset management — background tint, left border accent, warna konsisten (Total Barang → biru `#4e73df`, Stok Menipis → oranye `#fd7e14`, Distribusi Pending → kuning `#ffc107`).
- **Sidebar Language:** Sidebar diseragamkan ke bahasa Inggris (Laporan Asset → Asset Reports, Transaksi In/Out → Transactions, Distribusi → Distributions, Laporan Consumable → Consumable Reports, Master Supplier → Suppliers, Riwayat Aktivitas → Activity Logs).
- **Master Data Search & Scroll:** Tiap tabel master data (Kategori, Divisi, Pemegang, Lokasi, Kategori Consumable, Satuan Consumable) ditambahkan kolom search filter by nama, dibatasi tampil ~15 baris dengan scroll.
- **Import Date Fix:** `parseDate()` di `ProductImport` tidak lagi return `now()` untuk tanggal kosong — sekarang return `null`, sehingga produk tanpa `warranty_expiry_date` tidak masuk bell kuning.
- **Activity Log Import:** Produk & Consumable import sekarang tercatat di activity log (`Activity::log('asset'/'consumable', 'import', ...)`).
- **SKU Consumable:** Kolom `sku` (format `CSM-00000001`) ditambahkan ke `consumable_items`. Auto-generate di controller store & import. Ditampilkan di DataTable master barang.
- **No Column Removed:** Kolom "No" (DT_RowIndex) dihapus dari DataTable consumable items — konsisten dengan product yang juga tidak pakai nomor baris.
- **Supplier Import Excel:** Fitur download template & import Excel untuk Supplier. File: `SupplierImport`, `SupplierTemplateExport`. Route: `suppliers.import`, `suppliers.import_template`.
