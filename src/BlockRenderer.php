<?php

namespace Haringsrob\FilamentPageBuilder;

use Haringsrob\FilamentPageBuilder\Blocks\BlockEditorBlock;
use Haringsrob\FilamentPageBuilder\Models\Block;
use Illuminate\Contracts\View\View;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use SplFileInfo;

class BlockRenderer
{
    private ?array $cache = null;

    public function __construct(protected Filesystem $filesystem)
    {
    }

    /**
     * @return class-string<BlockEditorBlock>[]
     */
    public function getAllBlocks(): array
    {
        if ($this->cache) {
            return $this->cache;
        }

        $blocksDirectory = app_path('Filament/Blocks');

        if ((! $this->filesystem->exists($blocksDirectory))) {
            return [];
        }

        $namespace = Str::of(config('filament.resources.namespace'))->beforeLast('\\')->append('\\Blocks*');

        $classes = collect($this->filesystem->allFiles($blocksDirectory))
            ->map(function (SplFileInfo $file) use ($namespace) {
                $variableNamespace = $namespace->contains('*') ? str_ireplace(
                    ['\\' . $namespace->before('*'), $namespace->after('*')],
                    ['', ''],
                    Str::of($file->getPath())
                        ->after(base_path())
                        ->replace(['/'], ['\\']),
                ) : null;

                if (is_string($variableNamespace)) {
                    $variableNamespace = (string)Str::of($variableNamespace)->before('\\');
                }

                return (string)$namespace
                    ->append('\\', $file->getRelativePathname())
                    ->replace('*', $variableNamespace)
                    ->replace(['/', '.php'], ['\\', '']);
            });

        /** @var class-string<BlockEditorBlock> $class */
        foreach ($classes as $class) {
            $this->cache[$class::getSystemName()] = $class;
        }

        return $this->cache;
    }

    public function renderBlock(Block $block): string|View
    {
        /** @var class-string<BlockEditorBlock> $class */
        if ($class = ($this->getAllBlocks()[$block->type] ?? false)) {
            return $class::make($block->type)->renderDisplay([...$block->content, ...$block->shared]);
        }

        return '';
    }
}
