<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BahanBaku extends Model
{
    protected $table = 'bahan_baku';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'tenant_id', 'nama', 'stok', 'satuan',
        'tipe', 'tgl_masuk', 'tgl_kadaluarsa', 'gambar',
    ];

    protected $casts = [
        'tgl_masuk'       => 'date',
        'tgl_kadaluarsa'  => 'date',
        'stok'            => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function menuBahanBakus()
    {
        return $this->hasMany(MenuBahanBaku::class, 'bahan_baku_id');
    }
}