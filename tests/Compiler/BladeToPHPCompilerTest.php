<?php

declare(strict_types=1);

namespace Vural\PHPStanBladeRule\Tests\Compiler;

use Generator;
use PHPStan\Testing\PHPStanTestCase;
use Vural\PHPStanBladeRule\Compiler\BladeToPHPCompiler;

use function array_merge;
use function file_get_contents;
use function trim;

/** @covers \Vural\PHPStanBladeRule\Compiler\FileNameAndLineNumberAddingPreCompiler */
class BladeToPHPCompilerTest extends PHPStanTestCase
{
    private BladeToPHPCompiler $compiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->compiler = self::getContainer()->getByType(BladeToPHPCompiler::class);
    }

    /** @return string[] */
    public static function getAdditionalConfigFiles(): array
    {
        return array_merge(parent::getAdditionalConfigFiles(), [__DIR__ . '/config/bladeToPHPCompilerTest.neon']);
    }

    /**
     * @test
     * @dataProvider basicTemplateProvider
     */
    public function it_compiles_blade_content(string $fileName): void
    {
        $result = $this->compiler->compileContent(__DIR__ . '/data/' . $fileName . '.blade.php', []);

        $this->assertEquals(trim(file_get_contents(__DIR__ . '/data/' . $fileName . '-blade-compiled.php')), trim($result->getPhpFileContents()));
    }

    public function basicTemplateProvider(): Generator
    {
        yield 'Single line template' => ['single-line'];
        yield 'Multiple lines template' => ['multiple-lines'];
        yield 'Multiple lines template with HTML' => ['multiple-lines-with-html'];
        yield 'Two PHP tags in the same line' => ['two-php-tags-in-the-same-line'];
        yield 'Single line @php directive' => ['single-line-at-php-directive'];
        yield 'Multiple lines @php directive' => ['multiple-lines-at-php-directive'];
        yield 'Multiple lines @php directive with comments' => ['multiple-lines-at-php-directive-with-comments'];
    }
}
