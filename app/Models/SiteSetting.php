<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['key', 'value'];

    public static function getValue(string $key, string $default = ''): string
    {
        return static::query()->whereKey($key)->value('value') ?? $default;
    }
}
