<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class perfil extends Model
{
  public $timestamps = false;
  protected $fillable = ['nombre', 'is_admin', 'is_student'];
}