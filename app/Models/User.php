<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class User extends Authenticatable
{
    use Notifiable, HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // super_admin, admin, trainer, trainee
        'google_id',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'trainee_id');
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class, 'trainer_id');
    }

    public function schedules()
    {
        return $this->hasMany(TrainingSchedule::class, 'trainer_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}