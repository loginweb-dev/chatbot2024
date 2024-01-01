<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Grupo extends Model
{
    protected $fillable = ['nombre', 'descripcion', 'codigo', 'bot', 'admin'];
}
