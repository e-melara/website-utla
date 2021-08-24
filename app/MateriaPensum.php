<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MateriaPensum extends Model
{
    public $primaryKey = 'codmate';
    public $table = 'materiaspensum';

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class, 'codmate', 'codmate');
    }
}
