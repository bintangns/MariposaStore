<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'rank_name',     // LuckPerms group name, dipakai untuk auto-generate command durasi
        'description',
        'price',
        'image',
        'category_id',
        'commands',      // JSON array of commands to run after purchase
        'is_active',
        'sort_order',
        'features',      // JSON array of features to display
        'color',         // accent color for card
    ];

    protected $casts = [
        'commands' => 'array',
        'features' => 'array',
        'is_active' => 'boolean',
        'price' => 'integer',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function durations()
    {
        return $this->hasMany(ProductDuration::class)->orderBy('sort_order');
    }

    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
