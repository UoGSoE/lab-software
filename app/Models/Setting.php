<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use App\Models\Scopes\AcademicSessionScope;

#[ScopedBy([AcademicSessionScope::class])]
class Setting extends Model
{
    /** @use HasFactory<\Database\Factories\SettingFactory> */
    use HasFactory;

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
