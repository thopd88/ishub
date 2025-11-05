<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'student_parent_relationships',
            'parent_id',
            'student_id'
        );
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'student_parent_relationships',
            'student_id',
            'parent_id'
        );
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(\Modules\School\Models\Submission::class, 'student_id');
    }

    public function assignmentsAsTeacher(): HasMany
    {
        return $this->hasMany(\Modules\School\Models\Assignment::class, 'teacher_id');
    }
}
