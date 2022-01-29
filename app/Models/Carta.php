<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carta extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

   /* public function colecciones(){
        return $this->belongsToMany(Coleccion::class,'cartas_colecciones');
    }*/
}