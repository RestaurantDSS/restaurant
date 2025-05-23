<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = ['nombre_cli', 'cedula_cli', 'email_cli', 'telefono_cli'];

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'id_cli');
    }
}
