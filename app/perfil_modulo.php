<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class perfil_modulo extends Model
{
  public $timestamps = false;
  protected $fillable = ['perfil_id', 'modulo_id'];
}
