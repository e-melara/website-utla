<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StudentEnrolledSubjects extends Model
{
    public $timestamps = false;
    protected $fillable = [  "codcarga", "estado", 'student_enrolled_id'];
    
    public function enrolled()
    {
        return $this->belongsTo(StudentEnrolled::class);
    }
}
