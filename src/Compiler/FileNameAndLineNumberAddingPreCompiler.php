<?php

declare(strict_types=1);

namespace Vural\PHPStanBladeRule\Compiler;

use Illuminate\Support\Str;

use function explode;
use function implode;
use function rtrim;
use function sprintf;
use function str_contains;

use const PHP_EOL;

final class FileNameAndLineNumberAddingPreCompiler
{
    private string $fileName;

    /**
     * @param string[] $templatePaths
     */
    public function __construct(
        private array $templatePaths = [],
    ) {
    }

    public function compileString(string $value): string
    {
        $lines = explode(PHP_EOL, $value);

        $lineNumber = 1;

        foreach ($lines as $key => $line) {
            $lines[$key] = sprintf('/** file: %s, line: %d */', $this->fileName, $lineNumber) . $line;

            $lineNumber++;
        }

        return implode(PHP_EOL, $lines);
    }

    public function setFileName(string $fileName): self
    {
        foreach ($this->templatePaths as $templatePath) {
            $templatePath = rtrim($templatePath, '/') . '/';

            if (str_contains($fileName, $templatePath)) {
                $fileName = Str::after($fileName, $templatePath);
                break;
            }
        }

        $this->fileName = $fileName;

        return $this;
    }
}
