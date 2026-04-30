<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class BahanMaster extends Model
{
    protected $table = 'bahan_master';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['id', 'usaha_id', 'nama', 'satuan', 'harga_default', 'gambar'];
    protected $casts    = ['harga_default' => 'decimal:2'];

    public function usaha()        { return $this->belongsTo(Usaha::class, 'usaha_id'); }
    public function bahanOutlets() { return $this->hasMany(BahanOutlet::class, 'bahan_master_id'); }
}