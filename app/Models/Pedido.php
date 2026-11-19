<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    use HasFactory;

    public const ESTADOS = ['pendiente', 'confirmado', 'enviado', 'entregado', 'cancelado'];

    // Transiciones permitidas del admin
    public const TRANSICIONES = [
        'pendiente'  => ['confirmado', 'cancelado'],
        'confirmado' => ['enviado', 'cancelado'],
        'enviado'    => ['entregado'],
        'entregado'  => [],
        'cancelado'  => [],
    ];

    protected $fillable = ['user_id', 'estado', 'total', 'direccion_envio'];

    protected $casts = [
        'total' => 'float',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(PedidoDetalle::class);
    }
}
