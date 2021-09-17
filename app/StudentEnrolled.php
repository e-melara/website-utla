<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StudentEnrolled extends Model
{
  protected $primaryKey = 'id';
  protected $fillable = ['carnet', 'ciclo', 'observacion', 'estado'];

  public function schules()
  {
    return $this->hasMany(StudentEnrolledSubjects::class);
  }
}
