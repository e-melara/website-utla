<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
  protected $fillable = [
    'student_enrolled_id',
    'banco_id',
    'nombre_titular',
    'is_titular',
    'monto',
    'fecha_pago',
    'concepto',
  ];

  public function aranceles()
  {
    return $this->hasMany(PagoAranceles::class);
  }
  public function archivos()
  {
    return $this->hasMany(PagoArchivos::class);
  }
  public function banco()
  {
    return $this->belongsTo(Banco::class);
  }
  public function toData()
  {
    return [
      "id"  => $this->id,
      "monto"  => $this->monto,
      "student_enrolled_id" => $this->student_enrolled_id
    ];
  }
}
