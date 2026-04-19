<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriMenu extends Model
{
    protected $table = 'kategori_menu';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'usaha_id', 'nama'];

    public function usaha()
    {
        return $this->belongsTo(Usaha::class, 'usaha_id');
    }

    public function menus()
    {
        return $this->hasMany(Menu::class, 'kategori_id');
    }
}