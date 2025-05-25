<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';

    protected $fillable = ['fecha_ped', 'total_ped', 'modoPago_ped', 'estado_ped', 'id_cli'];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cli');
    }

    public function peDetalles(): HasMany
    {
        return $this->hasMany(PeDetalle::class, 'id_ped');
    }

    protected function casts(): array
    {
        return [
            'pe_detalles' => 'array',
        ];
    }
}
