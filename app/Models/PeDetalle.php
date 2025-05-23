<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeDetalle extends Model
{
    use HasFactory;

    protected $fillable = ['precio_pdet', 'cantidad_pdet', 'subtotal_pdet', 'id_ped', 'id_pro'];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_ped');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_pro');
    }
}
