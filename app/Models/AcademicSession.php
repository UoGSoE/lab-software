<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicSession extends Model
{
    /** @use HasFactory<\Database\Factories\AcademicSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public static function getDefault(): ?AcademicSession
    {
        return self::where('is_default', true)->first();
    }

    public function setAsDefault(): void
    {
        $this->is_default = true;
        $this->save();
    }
}
