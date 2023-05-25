<?php

namespace Haringsrob\FilamentPageBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Translatable\HasTranslations;

class Block extends Model
{
    // @todo: Translations is a full todo.
    use HasTranslations;

    public $translatable = ['content'];

    protected $fillable = [
        'content',
        'type',
        'position',
    ];

    protected $casts = [
        'content' => 'array',
    ];

    public function blockable(): MorphTo
    {
        return $this->morphTo();
    }
}
