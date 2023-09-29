<?php

namespace Sevendays\FilamentPageBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Translatable\HasTranslations;

class Block extends Model
{
    use HasTranslations;

    public $translatable = ['content'];

    protected $fillable = [
        'content',
        'shared',
        'type',
        'position',
    ];

    protected $casts = [
        'content' => 'array',
        'shared' => 'array',
    ];

    public function blockable(): MorphTo
    {
        return $this->morphTo();
    }
}
