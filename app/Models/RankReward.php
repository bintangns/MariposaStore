<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RankReward extends Model
{
    protected $fillable = ['product_id', 'gradient_count', 'custom_count'];

    protected $casts = [
        'gradient_count' => 'integer',
        'custom_count' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
