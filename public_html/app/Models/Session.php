<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;
    protected $table = 'sessions';
    protected $fillable = [
        'session_name',
        'session_value',
        'created_by',
        'status',
        'batch_id'
    ];

    public function Batch()
    {
        return $this->belongsTo(Batch::class);
    }
}
