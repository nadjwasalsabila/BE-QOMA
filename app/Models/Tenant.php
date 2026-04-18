<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $table = 'tenant';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'usaha_id', 'nama_cabang', 'alamat', 'status_buka',
    ];

    protected $casts = [
        'status_buka' => 'boolean',
    ];

    // Tenant milik 1 usaha
    public function usaha()
    {
        return $this->belongsTo(Usaha::class, 'usaha_id');
    }

    // Tenant punya banyak kasir (users dengan role kasir)
    public function kasirs()
    {
        return $this->hasMany(User::class, 'tenant_id')
                    ->whereHas('role', fn($q) => $q->where('name', 'kasir'));
    }
}