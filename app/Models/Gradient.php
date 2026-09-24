<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gradient extends Model
{
    protected $fillable = ['name', 'colors', 'sort_order'];

    protected $casts = [
        'colors' => 'array',
    ];

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Terapkan gradient ini ke sebuah teks, hasilnya string dengan kode warna
     * hex per-karakter (format &#RRGGBB, didukung Spigot/Paper 1.16+ &
     * EssentialsX modern). Interpolasi linear RGB antar color stop.
     */
    public function apply(string $text): string
    {
        $stops = array_values($this->colors ?? []);
        $length = mb_strlen($text);

        if (count($stops) < 2 || $length === 0) {
            return $text;
        }

        $segments = count($stops) - 1;
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($text, $i, 1);
            $position = $length === 1 ? 0 : $i / ($length - 1);
            $segmentPos = $position * $segments;
            $segmentIndex = (int) floor($segmentPos);

            if ($segmentIndex >= $segments) {
                $segmentIndex = $segments - 1;
                $fraction = 1.0;
            } else {
                $fraction = $segmentPos - $segmentIndex;
            }

            $from = self::hexToRgb($stops[$segmentIndex]);
            $to   = self::hexToRgb($stops[$segmentIndex + 1]);

            $r = (int) round($from[0] + ($to[0] - $from[0]) * $fraction);
            $g = (int) round($from[1] + ($to[1] - $from[1]) * $fraction);
            $b = (int) round($from[2] + ($to[2] - $from[2]) * $fraction);

            $result .= sprintf('&#%02X%02X%02X%s', $r, $g, $b, $char);
        }

        return $result;
    }

    private static function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }
}
