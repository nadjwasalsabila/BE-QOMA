<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Outlet extends Model
{
    protected $table = 'outlet';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id', 'usaha_id', 'nama_outlet', 'alamat', 'email', 'status_buka'];

    protected $casts = ['status_buka' => 'boolean'];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($m) => $m->id = $m->id ?: Str::uuid());
    }

    public function usaha()
    {
        return $this->belongsTo(Usaha::class, 'usaha_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'outlet_id');
    }

    public function mejas()
    {
        return $this->hasMany(Meja::class, 'outlet_id');
    }

    public function pesanans()
    {
        return $this->hasMany(Pesanan::class, 'outlet_id');
    }

    public function bahanOutlets()
    {
        return $this->hasMany(BahanOutlet::class, 'outlet_id');
    }

    public function menuOutlets()
    {
        return $this->hasMany(MenuOutlet::class, 'outlet_id');
    }

    public function pengeluarans()
    {
        return $this->hasMany(Pengeluaran::class, 'outlet_id');
    }
}