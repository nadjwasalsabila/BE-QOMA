<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BahanMaster extends Model
{
    protected $table = 'bahan_master';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id','usaha_id','nama','satuan','harga_default','gambar'];

    protected static function boot() {
        parent::boot();
        static::creating(fn($m) => $m->id = $m->id ?: Str::uuid());
    }

    public function usaha()        { return $this->belongsTo(Usaha::class, 'usaha_id'); }
    public function menuBahans()   { return $this->hasMany(MenuBahan::class, 'bahan_master_id'); }
    public function bahanOutlets() { return $this->hasMany(BahanOutlet::class, 'bahan_master_id'); }
}