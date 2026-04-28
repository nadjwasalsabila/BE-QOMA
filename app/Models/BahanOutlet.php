<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BahanOutlet extends Model
{
    protected $table = 'bahan_outlet';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id','outlet_id','bahan_master_id','stok','tanggal_masuk','tanggal_kadaluarsa'];

    protected $casts = [
        'tanggal_masuk'       => 'date',
        'tanggal_kadaluarsa'  => 'date',
    ];

    protected static function boot() {
        parent::boot();
        static::creating(fn($m) => $m->id = $m->id ?: Str::uuid());
    }

    public function outlet()      { return $this->belongsTo(Outlet::class, 'outlet_id'); }
    public function bahanMaster() { return $this->belongsTo(BahanMaster::class, 'bahan_master_id'); }

    // Alert: stok menipis < 10
    public function isMenuipis(): bool  { return $this->stok < 10; }
    // Alert: stok kritis < 3
    public function isKritis(): bool    { return $this->stok < 3; }
    // Alert: kadaluarsa dalam 3 hari
    public function isMendekatiKadaluarsa(): bool {
        return $this->tanggal_kadaluarsa && $this->tanggal_kadaluarsa->diffInDays(now()) <= 3;
    }
}