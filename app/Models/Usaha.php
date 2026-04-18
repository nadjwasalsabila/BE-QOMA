<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usaha extends Model
{
    protected $table = 'usaha';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'nama_usaha', 'owner_id', 'email',
    ];

    // 1 usaha punya banyak cabang (tenant)
    public function tenants()
    {
        return $this->hasMany(Tenant::class, 'usaha_id');
    }

    // 1 usaha dimiliki 1 owner (user)
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}