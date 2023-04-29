<?php

namespace Haringsrob\FilamentPageBuilder\Commands;

use Closure;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakePageBuilderBlock extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:page-builder-block {name} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make page builder block';

/**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'FilamentPageBuilderBlock';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (parent::handle() === false && ! $this->option('force')) {
            return false;
        }

        return $this->writeView();
    }

    /**
     * Parse the class name and format according to the root namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function qualifyClass($name)
    {
        $name = ltrim($name, '\\/');

        $name = str_replace('/', '\\', $name);

        $rootNamespace = $this->rootNamespace() . '\\Filament\\Blocks';

        if (Str::startsWith($name, $rootNamespace)) {
            return Str::replace('\\\\', '\\', $name);
        }

        return $this->qualifyClass(
            $this->getDefaultNamespace(trim($rootNamespace, '\\')).'\\'.$name
        );
    }

    /**
     * Write the view for the component.
     */
    protected function writeView(?Closure $onSuccess = null): void
    {
        $path = $this->viewPath(
            str_replace('.', '/', 'filament.blocks.'.$this->getView()).'.blade.php'
        );

        if (! $this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }

        if ($this->files->exists($path) && ! $this->option('force')) {
            $this->components->error('View already exists.');

            return;
        }

        file_put_contents(
            $path,
            '<div>
    {{$title ?? "No title set"}}
</div>'
        );

        if ($onSuccess) {
            $onSuccess();
        }
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        return str_replace(
            ['DummyView', '{{ view }}'],
            'view(\'filament.blocks.'.$this->getView().'\', $state)',
            parent::buildClass($name)
        );
    }

    /**
     * Get the view name relative to the components directory.
     */
    protected function getView(): string
    {
        $name = str_replace('\\', '/', $this->argument('name'));

        return collect(explode('/', $name))
            ->map(function ($part) {
                return Str::kebab($part);
            })
            ->implode('.');
    }

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return $this->resolveStubPath('/stubs/block.stub');
    }

    /**
     * Resolve the fully-qualified path to the stub.
     */
    protected function resolveStubPath(string $stub): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
                        ? $customPath
                        : __DIR__.$stub;
    }
}
