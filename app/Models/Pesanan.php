<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Pesanan extends Model
{
    protected $table = 'pesanan';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id','outlet_id','meja_id','nama_pelanggan','no_telp','total_harga','status'];

    protected static function boot() {
        parent::boot();
        static::creating(fn($m) => $m->id = $m->id ?: Str::uuid());
    }

    public function outlet()        { return $this->belongsTo(Outlet::class, 'outlet_id'); }
    public function meja()          { return $this->belongsTo(Meja::class, 'meja_id'); }
    public function detils()        { return $this->hasMany(PesananDetil::class, 'pesanan_id'); }
    public function pembayaran()    { return $this->hasOne(Pembayaran::class, 'pesanan_id'); }
}