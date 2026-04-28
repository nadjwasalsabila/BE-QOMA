<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Usaha extends Model
{
    protected $table = 'usaha';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id', 'nama_usaha', 'email', 'alamat', 'owner_id'];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($m) => $m->id = $m->id ?: Str::uuid());
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function outlets()
    {
        return $this->hasMany(Outlet::class, 'usaha_id');
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class, 'usaha_id')->latestOfMany();
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'usaha_id');
    }

    public function menus()
    {
        return $this->hasMany(Menu::class, 'usaha_id');
    }

    public function bahanMasters()
    {
        return $this->hasMany(BahanMaster::class, 'usaha_id');
    }
}