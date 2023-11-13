<?php

namespace Sevendays\FilamentPageBuilder\Models\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Sevendays\FilamentPageBuilder\Models\Block;

trait HasBlocks
{
    public function blocks(): MorphMany
    {
        return $this->morphMany(Block::class, 'blockable');
    }
}
