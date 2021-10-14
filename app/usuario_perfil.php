<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class usuario_perfil extends Model
{
  public $timestamps = false;
  protected $fillable = ['usuario_id', 'perfil_id', 'estado'];
}
