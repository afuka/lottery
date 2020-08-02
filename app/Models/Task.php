<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $casts = [
        'params' => 'json',
    ];

    protected $fillable = [
        'operator_id', 'type', 'name', 'memo', 'params'
    ];
}
