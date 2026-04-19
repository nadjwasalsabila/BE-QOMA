<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'usaha_id', 'kategori_id',
        'nama', 'harga_default', 'gambar', 'keterangan', 'is_active',
    ];

    protected $casts = [
        'harga_default' => 'decimal:2',
        'is_active'     => 'boolean',
    ];

    // Relasi ke kategori
    public function kategori()
    {
        return $this->belongsTo(KategoriMenu::class, 'kategori_id');
    }

    // Relasi ke usaha
    public function usaha()
    {
        return $this->belongsTo(Usaha::class, 'usaha_id');
    }

    // Relasi ke bahan baku (via pivot menu_bahan_baku)
    public function bahanBakus()
    {
        return $this->belongsToMany(
            BahanBaku::class,
            'menu_bahan_baku',
            'menu_id',
            'bahan_baku_id'
        )->withPivot('jumlah_pakai')->withTimestamps();
    }

    // Relasi ke harga per cabang
    public function menuTenants()
    {
        return $this->hasMany(MenuTenant::class, 'menu_id');
    }

    // Helper: ambil harga untuk tenant tertentu
    public function hargaUntukTenant(string $tenantId): float
    {
        $mt = $this->menuTenants->where('tenant_id', $tenantId)->first();
        return $mt ? (float) $mt->harga : (float) $this->harga_default;
    }
}