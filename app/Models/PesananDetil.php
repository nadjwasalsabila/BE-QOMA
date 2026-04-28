<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PesananDetil extends Model
{
    protected $table = 'pesanan_detil';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['id','pesanan_id','menu_id','qty','harga'];

    protected static function boot() {
        parent::boot();
        static::creating(fn($m) => $m->id = $m->id ?: Str::uuid());
    }

    public function pesanan() { return $this->belongsTo(Pesanan::class, 'pesanan_id'); }
    public function menu()    { return $this->belongsTo(Menu::class, 'menu_id'); }
    public function addons()  { return $this->hasMany(PesananAddon::class, 'pesanan_detil_id'); }
}