<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
  public $timestamps = false;
  protected $table = 'solicitudes';
  protected $fillable = ['type', 'carnet', 'observacion', 'codmate', 'estado', 'ciclo'];

  public function carga_academica()
  {
    return $this->hasOne(SolicitudesCargasAcademica::class);
  }

  public function materia()
  {
    return $this->hasOne(MateriaPensum::class, 'codmate', 'codmate');
  }
}
