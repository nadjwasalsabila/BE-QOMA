<?php
// ====================== Menu.php ======================
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Menu extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id','usaha_id','kategori_id','nama','harga_default','gambar','keterangan'];

    protected static function boot() {
        parent::boot();
        static::creating(fn($m) => $m->id = $m->id ?: Str::uuid());
    }

    public function usaha()      { return $this->belongsTo(Usaha::class, 'usaha_id'); }
    public function kategori()   { return $this->belongsTo(KategoriMenu::class, 'kategori_id'); }
    public function menuBahans() { return $this->hasMany(MenuBahan::class, 'menu_id'); }
    public function menuOutlets(){ return $this->hasMany(MenuOutlet::class, 'menu_id'); }
}