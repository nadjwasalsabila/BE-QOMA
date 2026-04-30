<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MenuOutlet extends Model
{
    protected $table = 'menu_outlet';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['id', 'menu_id', 'outlet_id', 'harga', 'is_available'];
    protected $casts    = ['harga' => 'decimal:2', 'is_available' => 'boolean'];

    public function menu()   { return $this->belongsTo(Menu::class, 'menu_id'); }
    public function outlet() { return $this->belongsTo(Outlet::class, 'outlet_id'); }
}