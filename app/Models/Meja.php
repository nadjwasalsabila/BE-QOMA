<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Meja extends Model
{
    protected $table = 'meja';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id','outlet_id','nomor_meja','qr_code'];

    protected static function boot() {
        parent::boot();
        static::creating(fn($m) => $m->id = $m->id ?: Str::uuid());
    }

    public function outlet()   { return $this->belongsTo(Outlet::class, 'outlet_id'); }
    public function pesanans() { return $this->hasMany(Pesanan::class, 'meja_id'); }
}