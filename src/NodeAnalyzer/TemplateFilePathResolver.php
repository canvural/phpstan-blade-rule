<?php

declare(strict_types=1);

namespace Vural\PHPStanBladeRule\NodeAnalyzer;

use Illuminate\View\ViewFinderInterface;
use Illuminate\View\ViewName;
use InvalidArgumentException;
use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use Vural\PHPStanBladeRule\Laravel\LaravelContainer;

use function file_exists;
use function is_string;

/** @see \Symplify\TemplatePHPStanCompiler\NodeAnalyzer\TemplateFilePathResolver */
final class TemplateFilePathResolver
{
    private ViewFinderInterface $viewFinder;

    public function __construct(
        LaravelContainer $laravelContainer,
        private ValueResolver $valueResolver,
    ) {
        $this->viewFinder = $laravelContainer->viewFinder();
    }

    /** @return string[] */
    public function resolveExistingFilePaths(Expr $expr, Scope $scope): array
    {
        $resolvedValue = $this->valueResolver->resolve($expr, $scope);

        if (! is_string($resolvedValue)) {
            return [];
        }

        $resolvedValue = $this->normalizeName($resolvedValue);
        if (file_exists($resolvedValue)) {
            return [$resolvedValue];
        }

        $view = $this->findView($resolvedValue);

        if ($view === null) {
            return [];
        }

        return [$view];
    }

    private function normalizeName(string $name): string
    {
        return ViewName::normalize($name);
    }

    private function findView(string $view): ?string
    {
        try {
            return $this->viewFinder->find($view);
        } catch (InvalidArgumentException) {
            return null;
        }
    }
}
