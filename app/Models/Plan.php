<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $table = 'plans';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['id', 'nama_plan', 'harga', 'batas_outlet', 'deskripsi'];
    protected $casts    = ['harga' => 'decimal:2', 'batas_outlet' => 'integer'];

    public function subscriptions() { return $this->hasMany(Subscription::class, 'plan_id'); }
}