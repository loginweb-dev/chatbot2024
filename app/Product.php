<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Product extends Model
{
    protected $fillable = ['user_id', 'credit', 'supplier_id', 'image', 'name'];
}
