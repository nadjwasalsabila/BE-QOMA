<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MenuOutlet extends Model
{
    protected $table = 'menu_outlet';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id','menu_id','outlet_id','harga'];

    protected static function boot() {
        parent::boot();
        static::creating(fn($m) => $m->id = $m->id ?: Str::uuid());
    }

    public function menu()   { return $this->belongsTo(Menu::class, 'menu_id'); }
    public function outlet() { return $this->belongsTo(Outlet::class, 'outlet_id'); }
}