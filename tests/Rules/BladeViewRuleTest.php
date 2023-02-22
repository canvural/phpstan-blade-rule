<?php

declare(strict_types=1);

namespace Vural\PHPStanBladeRule\Tests\Rules;

use PHPStan\Rules\Operators\InvalidBinaryOperationRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Symplify\TemplatePHPStanCompiler\PHPStan\FileAnalyserProvider;
use Symplify\TemplatePHPStanCompiler\TypeAnalyzer\TemplateVariableTypesResolver;
use Vural\PHPStanBladeRule\Compiler\BladeToPHPCompiler;
use Vural\PHPStanBladeRule\ErrorReporting\Blade\TemplateErrorsFactory;
use Vural\PHPStanBladeRule\NodeAnalyzer\BladeViewMethodsMatcher;
use Vural\PHPStanBladeRule\NodeAnalyzer\LaravelViewFunctionMatcher;
use Vural\PHPStanBladeRule\Rules\BladeRule;
use Vural\PHPStanBladeRule\Rules\ViewRuleHelper;

use function array_merge;

/**
 * @extends RuleTestCase<BladeRule>
 */
class BladeViewRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new BladeRule(
            [self::getContainer()->getByType(InvalidBinaryOperationRule::class)],
            self::getContainer()->getByType(BladeViewMethodsMatcher::class),
            self::getContainer()->getByType(LaravelViewFunctionMatcher::class),
            self::getContainer()->getByType(ViewRuleHelper::class),
        );
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/view-factory.php'], [
            [
                'Binary operation "+" between string and 10 results in an error.',
                13,
            ],
            [
                'Binary operation "+" between string and \'bar\' results in an error.',
                13,
            ],
            [
                'Binary operation "+" between string and 10 results in an error.',
                14,
            ],
            [
                'Binary operation "+" between string and \'bar\' results in an error.',
                14,
            ],
            [
                'Binary operation "+" between string and 10 results in an error.',
                15,
            ],
            [
                'Binary operation "+" between string and \'bar\' results in an error.',
                15,
            ],
        ]);
    }

    /** @return string[] */
    public static function getAdditionalConfigFiles(): array
    {
        return array_merge(parent::getAdditionalConfigFiles(), [__DIR__ . '/config/configWithTemplatePaths.neon']);
    }
}
