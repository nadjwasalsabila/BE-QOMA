<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Addon extends Model
{
    protected $table = 'addon';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id','usaha_id','nama','harga'];

    protected static function boot() {
        parent::boot();
        static::creating(fn($m) => $m->id = $m->id ?: Str::uuid());
    }

    public function usaha() { return $this->belongsTo(Usaha::class, 'usaha_id'); }
}