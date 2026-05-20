<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeldBy extends Model
{
    protected $table = 'held_bies'; 
    protected $fillable = ['name'];
}
