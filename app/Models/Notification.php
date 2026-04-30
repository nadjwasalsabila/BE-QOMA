<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['id', 'user_id', 'title', 'message', 'is_read', 'type', 'data'];
    protected $casts    = ['is_read' => 'boolean', 'data' => 'array'];

    public function user() { return $this->belongsTo(User::class, 'user_id'); }
}