<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'padre_id'];

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }

    public function subcategorias(): HasMany
    {
        return $this->hasMany(Categoria::class, 'padre_id');
    }

    public function padre()
    {
        return $this->belongsTo(Categoria::class, 'padre_id');
    }
}
