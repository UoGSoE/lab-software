<?php

namespace App\Models;

use App\Models\Scopes\AcademicSessionScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

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
