<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistributionDetail extends Model
{
    protected $guarded = ['id'];

    public function header()
    {
        return $this->belongsTo(DistributionHeader::class, 'distribution_header_id');
    }

    public function consumableItem()
    {
        return $this->belongsTo(ConsumableItem::class, 'consumable_item_id');
    }
}
