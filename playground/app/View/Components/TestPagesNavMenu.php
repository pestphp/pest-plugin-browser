<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use Symfony\Component\Finder\SplFileInfo;

class TestPagesNavMenu extends Component
{
    public Collection $pages;
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->pages = collect(\File::allFiles(resource_path('views/test-pages')))
            ->map(fn (SplFileInfo $viewFile) => $viewFile->getFilename())
            ->map(fn (string $filename) => Str::chopEnd($filename, '.blade.php'));
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.test-pages-nav-menu');
    }
}
