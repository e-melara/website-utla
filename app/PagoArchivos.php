<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PagoArchivos extends Model
{
  public $timestamps = false;
  protected $fillable = ['pago_id', 'url', 'tipo'];

  public function pago()
  {
    return $this->belongsTo(Pago::class);
  }
}
