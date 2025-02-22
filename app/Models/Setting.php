<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\AcademicSessionScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Setting extends Model
{
    /** @use HasFactory<\Database\Factories\SettingFactory> */
    use HasFactory, AcademicSessionScope;

    protected $fillable = [
        'key',
        'value',
        'academic_session_id',
    ];

    public function toDate(): ?Carbon
    {
        return $this->value ? Carbon::parse($this->value) : null;
    }
}
