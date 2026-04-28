<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PesananAddon extends Model
{
    protected $table = 'pesanan_addon';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['id','pesanan_detil_id','addon_id','qty'];

    protected static function boot() {
        parent::boot();
        static::creating(fn($m) => $m->id = $m->id ?: Str::uuid());
    }

    public function pesananDetil() { return $this->belongsTo(PesananDetil::class, 'pesanan_detil_id'); }
    public function addon()        { return $this->belongsTo(Addon::class, 'addon_id'); }
}