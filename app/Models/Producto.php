<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use HasFactory;
    protected $fillable = ['categoria_id', 'nombre', 'descripcion', 'precio', 'stock', 'url_imagen', 'estado'];
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }
}
