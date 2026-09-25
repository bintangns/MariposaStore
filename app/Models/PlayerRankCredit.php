<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Catatan "player X udah pernah kena kredit reward dari beli produk Y" —
 * sekali per (username, product) selamanya, biar perpanjang/beli ulang
 * produk rank yang sama gak nambah jatah nickname gratis lagi.
 */
class PlayerRankCredit extends Model
{
    protected $fillable = ['minecraft_username', 'product_id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
