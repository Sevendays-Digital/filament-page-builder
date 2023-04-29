<?php

namespace Haringsrob\FilamentPageBuilder\Models\Traits;

use Haringsrob\FilamentPageBuilder\Models\Block;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @extends \Illuminate\Database\Eloquent\Model;
 */
trait HasBlocks
{
    public function blocks(): MorphMany
    {
        return $this->morphMany(Block::class, 'blockable');
    }
}
