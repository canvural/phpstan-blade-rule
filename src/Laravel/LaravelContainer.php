<?php

declare(strict_types=1);

namespace Vural\PHPStanBladeRule\Laravel;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\View\FileViewFinder;
use Illuminate\View\ViewFinderInterface;
use PHPStan\DependencyInjection\Container;
use PHPStan\File\FileHelper;

use function file_exists;

final class LaravelContainer
{
    private ?Application $laravelContainer = null;

    public function __construct(
        FileHelper $fileHelper,
        private Container $container,
    ) {
        // TODO add parameter in config to change this path
        $bootstrapPath = $fileHelper->absolutizePath('./bootstrap/app.php');

        if (! file_exists($bootstrapPath)) {
            return;
        }

        $this->laravelContainer = require $bootstrapPath;

        $this->laravelContainer->make(Kernel::class)->bootstrap();
    }

    public function viewFinder(): ViewFinderInterface
    {
        if ($this->laravelContainer) {
            return $this->laravelContainer->make(Factory::class)->getFinder();
        }

        return $this->container->getByType(FileViewFinder::class);
    }
}
