<?php

namespace Sevendays\FilamentPageBuilder;

use Filament\Facades\Filament;
use Illuminate\Contracts\View\View;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Sevendays\FilamentPageBuilder\Blocks\BlockEditorBlock;
use Sevendays\FilamentPageBuilder\Models\Block;
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

        $namespace = Str::of(Filament::getCurrentPanel()->getResourceNamespaces()[0])->beforeLast('\\')->append('\\Blocks*');

        $classes = collect($this->filesystem->allFiles($blocksDirectory))
            ->map(function (SplFileInfo $file) use ($namespace) {
                $variableNamespace = $namespace->contains('*') ? str_ireplace(
                    ['\\'.$namespace->before('*'), $namespace->after('*')],
                    ['', ''],
                    Str::of($file->getPath())
                        ->after(base_path())
                        ->replace(['/'], ['\\']),
                ) : null;

                if (is_string($variableNamespace)) {
                    $variableNamespace = (string) Str::of($variableNamespace)->before('\\');
                }

                return (string) $namespace
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
        /* @var class-string<BlockEditorBlock> $class */
        if ($class = ($this->getAllBlocks()[$block->type] ?? false)) {
            $pageContent = $block->content;
            //todo dirty hack to 'detect' if translations are in use ...
            if ($block->content === '') {
                $pageContent = $block->translations['content'];
            }
            $content = is_array($block->shared) ? [...$pageContent, ...$block->shared] : $pageContent;

            return $class::make($block->type)->renderDisplay($content);
        }

        return '';
    }
}
