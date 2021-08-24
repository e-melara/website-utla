<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SolicitudesCargasAcademica extends Model
{
    public $timestamps = false;
    protected $table = 'solicitudes_cargas_academicas';
    protected $fillable = ["solicitud_id", "codcarga"];

    public function solicitud()
    {
        return $this->hasOne(Solicitud::class);
    }
}
