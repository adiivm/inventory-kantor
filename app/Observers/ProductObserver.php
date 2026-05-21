<?php

namespace App\Observers;

use App\Models\Product;

class ProductObserver
{
    public function saving(Product $product): void
    {
        $product->stock = 1;

        if (empty($product->condition)) {
            $product->condition = 'ready';
        }
    }
}
