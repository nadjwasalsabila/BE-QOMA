<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuTenant extends Model
{
    protected $table = 'menu_tenant';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'menu_id', 'tenant_id', 'harga', 'is_available'];

    protected $casts = [
        'harga'        => 'decimal:2',
        'is_available' => 'boolean',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}