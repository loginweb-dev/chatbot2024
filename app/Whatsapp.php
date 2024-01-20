<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Whatsapp extends Model
{
    protected $fillable = ['nombre', 'codigo', 'estado', 'slug', 'telefono', 'logo', 'default'];
}
