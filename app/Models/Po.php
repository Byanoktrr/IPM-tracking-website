<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Po extends Model
{
    use HasFactory;

    protected $table = 'po';

    public $timestamps = false;

    protected $guarded = ['id'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'contacts' => 'array',
            'items' => 'array',
            'tgl_po' => 'date',
            'tgl_terima_po' => 'date',
            'tgl_terbit_do' => 'date',
            'deleverydate' => 'date',
            'jedahari' => 'integer',
        ];
    }

    /**
     * Calculate jeda hari between tgl_terima_po and deleverydate
     */
    public static function calculateJedaHari(?string $tglTerimaPo, ?string $deleverydate): ?int
    {
        if (empty($tglTerimaPo) || empty($deleverydate)) {
            return null;
        }

        try {
            $start = Carbon::parse($tglTerimaPo)->startOfDay();
            $end = Carbon::parse($deleverydate)->startOfDay();

            return (int) round($start->floatDiffInDays($end, false));
        } catch (\Throwable $e) {
            return null;
        }
    }
}
