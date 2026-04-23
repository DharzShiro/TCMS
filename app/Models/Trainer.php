<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trainer extends Model
{
    protected $fillable = [
        // Inherited from User: name, email, password, etc.
        'specialization',      // Trainer-specific
        'certification_number',
        'experience_years',
        'department',
    ];

    protected $attributes = ['role' => 'trainer'];

    public function assessments()
    {
        return $this->hasMany(Assessment::class, 'trainer_id');
    }

    public function attendanceRecords()
    {
        return $this->hasMany(Attendance::class, 'trainer_id');
    }

    public function classSchedules()
    {
        return $this->hasMany(ClassSchedule::class, 'trainer_id');
    }
}
