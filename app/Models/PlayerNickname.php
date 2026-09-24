<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerNickname extends Model
{
    protected $fillable = [
        'minecraft_username',
        'minecraft_uuid',
        'product_id',
        'order_id',
        'gradient_id',
        'type',        // 'custom' | 'gradient'
        'label',
        'value',
        'command_template',
        'is_active',
    ];

    protected $casts = [
        'command_template' => 'array',
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function gradient()
    {
        return $this->belongsTo(Gradient::class);
    }

    /**
     * Cuma nickname yang order-nya udah lunas (paid/delivered) yang boleh
     * muncul di inventory & bisa di-equip — order yang masih pending (belum
     * bayar / bukti belum diverifikasi admin) atau failed gak boleh kepake.
     */
    public function scopePaid($query)
    {
        return $query->whereHas('order', fn ($q) => $q->whereIn('status', ['paid', 'delivered']));
    }

    public function getIsPaidAttribute(): bool
    {
        return in_array($this->order?->status, ['paid', 'delivered'], true);
    }

    /**
     * Command RCON buat pasang nickname ini, sudah final (placeholder
     * {player}/{uuid}/{nickname} sudah disubstitusi & disnapshot pas dibeli
     * lewat Order::resolveCommands()) — dipakai lagi apa adanya tiap kali
     * nickname ini di-equip ulang dari inventory.
     */
    public function resolveCommands(): array
    {
        return $this->command_template ?? [];
    }
}
