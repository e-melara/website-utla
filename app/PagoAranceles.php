<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PagoAranceles extends Model
{
  public $timestamps = false;
  protected $fillable = ['pago_id', 'arancel_id', 'precio'];

  public function pago()
  {
    return $this->belongsTo(Pago::class);
  }
}
