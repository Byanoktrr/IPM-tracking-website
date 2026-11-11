<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_id',
        'ekspedisi',
        'linkekspedisi',
        'tanggal_kirim',
        'noresi',
        'no_ekspedisi',
        'fotoresi',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_kirim' => 'date',
        ];
    }

    public function po(): BelongsTo
    {
        return $this->belongsTo(Po::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShipmentItem::class);
    }
}
