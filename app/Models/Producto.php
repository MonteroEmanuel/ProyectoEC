<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Producto extends Model
{
    use HasFactory;
    protected $fillable = [
        'categoria_id',
        'marca_id', 
        'nombre',
        'descripcion',
        'precio',
        'stock',
        'url_imagen',
        'estado'
    ];
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }
    public function marca(): BelongsTo 
    {
        return $this->belongsTo(Marca::class);
    }
}