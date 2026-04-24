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
         'status', 'catatan_admin', 'approved_at', 'rejected_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
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

    public function rejections()
    {
        return $this->hasMany(UsahaRejection::class, 'usaha_id');
    }
}