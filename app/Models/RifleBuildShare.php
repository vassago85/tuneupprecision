<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\RifleBuildShareFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RifleBuildShare extends Model
{
    /** @use HasFactory<RifleBuildShareFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (RifleBuildShare $share): void {
            if (blank($share->code)) {
                $share->code = static::generateCode();
            }
        });
    }

    public static function generateCode(): string
    {
        do {
            $candidate = Str::lower(Str::random(8));
        } while (static::query()->where('code', $candidate)->exists());

        return $candidate;
    }
}
