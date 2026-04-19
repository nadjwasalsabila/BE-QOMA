<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuBahanBaku extends Model
{
    protected $table = 'menu_bahan_baku';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'menu_id', 'bahan_baku_id', 'jumlah_pakai'];

    protected $casts = [
        'jumlah_pakai' => 'decimal:2',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }
}