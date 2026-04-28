<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    protected $table = 'users';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'role_id', 'usaha_id', 'outlet_id',
        'username', 'email', 'password',
    ];

    protected $hidden = ['password'];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($m) => $m->id = $m->id ?: Str::uuid());
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function usaha()
    {
        return $this->belongsTo(Usaha::class, 'usaha_id');
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class, 'outlet_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role_id === 'superadmin';
    }

    public function isOwner(): bool
    {
        return $this->role_id === 'owner';
    }

    public function isOutlet(): bool
    {
        return $this->role_id === 'outlet';
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class, 'user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }
}