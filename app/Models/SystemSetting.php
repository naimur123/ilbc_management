<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'cast_type'];

    public function castValue(): mixed
    {
        return match ($this->cast_type) {
            'integer' => (int) $this->value,
            'decimal' => (float) $this->value,
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($this->value, true),
            default => $this->value,
        };
    }
}
