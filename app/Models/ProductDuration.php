<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductDuration extends Model
{
    protected $fillable = [
        'product_id',
        'label',
        'days',      // null = permanent
        'price',
        'sort_order',
    ];

    protected $casts = [
        'days'  => 'integer',
        'price' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    /**
     * Command LuckPerms untuk durasi ini. {player} diganti saat delivery.
     */
    public function buildCommand(string $rankName): string
    {
        return $this->days
            ? "lp user {player} parent addtemp {$rankName} {$this->days}d"
            : "lp user {player} parent set {$rankName}";
    }
}
