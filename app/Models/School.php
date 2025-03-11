<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class School extends Model
{
    /** @use HasFactory<\Database\Factories\SchoolFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'course_prefix',
    ];
}
