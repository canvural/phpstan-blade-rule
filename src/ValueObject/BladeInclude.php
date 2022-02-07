<?php

declare(strict_types=1);

namespace Vural\PHPStanBladeRule\ValueObject;

final class BladeInclude
{
    public function __construct(
        public string $name,
        public string $variables,
    ) {
    }
}
