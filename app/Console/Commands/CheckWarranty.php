<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class CheckWarranty extends Command
{
    protected $signature = 'inventory:check-warranty
                            {--expired : Tampilkan produk dengan garansi sudah expired}
                            {--limit=10 : Batas jumlah produk yang ditampilkan}';

    protected $description = 'Cek produk dengan garansi kritis (≤30 hari) atau expired';

    public function handle()
    {
        $showExpired = $this->option('expired');
        $limit = (int) $this->option('limit');

        if ($showExpired) {
            $products = Product::active()->warrantyExpired()
                ->with(['category', 'division'])
                ->limit($limit)
                ->get();

            if ($products->isEmpty()) {
                $this->info('✅ Tidak ada produk dengan garansi expired.');

                return Command::SUCCESS;
            }

            $this->warn('⚠️  Produk dengan garansi EXPIRED:');
        } else {
            $products = Product::active()->warrantyCritical()
                ->with(['category', 'division'])
                ->limit($limit)
                ->get();

            if ($products->isEmpty()) {
                $this->info('✅ Semua garansi produk dalam keadaan aman.');

                return Command::SUCCESS;
            }

            $this->warn('⚠️  Produk dengan garansi KRITIS (≤30 hari):');
        }

        $rows = $products->map(fn ($p) => [
            $p->sku,
            $p->name,
            $p->category?->name ?? '-',
            $p->division?->name ?? '-',
            $p->warranty_expiry_date?->format('d/m/Y') ?? '-',
            $showExpired ? 'EXPIRED' : now()->diffInDays($p->warranty_expiry_date).' hari',
        ]);

        $this->table(
            ['SKU', 'Nama', 'Kategori', 'Divisi', 'Garansi', 'Sisa'],
            $rows
        );

        $total = $showExpired
            ? Product::active()->warrantyExpired()->count()
            : Product::active()->warrantyCritical()->count();

        $this->info("Total: {$total} produk");

        return Command::SUCCESS;
    }
}
