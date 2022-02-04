<?php

declare(strict_types=1);

namespace Vural\PHPStanBladeRule\Tests\Compiler;

use Generator;
use PHPStan\Testing\PHPStanTestCase;
use Vural\PHPStanBladeRule\Compiler\FileNameAndLineNumberAddingPreCompiler;

use function array_merge;

/** @covers \Vural\PHPStanBladeRule\Compiler\FileNameAndLineNumberAddingPreCompiler */
class FileNameAndLineNumberAddingPreCompilerTest extends PHPStanTestCase
{
    private FileNameAndLineNumberAddingPreCompiler $compiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->compiler = self::getContainer()->getByType(FileNameAndLineNumberAddingPreCompiler::class);
    }

    /** @return string[] */
    public static function getAdditionalConfigFiles(): array
    {
        return array_merge(parent::getAdditionalConfigFiles(), [__DIR__ . '/config/configWithTemplatePaths.neon']);
    }

    /**
     * @test
     * @dataProvider basicTemplateProvider
     */
    function it_will_add_file_name_and_line_number_for_basic_templates(string $raw, string $expected): void
    {
        $this->compiler->setFileName('/var/www/resources/views/foo.blade.php');

        $this->assertSame(
            $expected,
            $this->compiler->compileString($raw)
        );
    }

    /** @test */
    function it_can_change_file_name_for_same_template(): void
    {
        $this->compiler->setFileName('/var/www/resources/views/foo.blade.php');

        $this->assertSame(
            '/** file: foo.blade.php, line: 1 */{{ $foo }}',
            $this->compiler->compileString('{{ $foo }}')
        );

        $this->compiler->setFileName('/var/www/resources/views/bar.blade.php');

        $this->assertSame(
            '/** file: bar.blade.php, line: 1 */{{ $foo }}',
            $this->compiler->compileString('{{ $foo }}')
        );
    }

    /** @test */
    function it_shows_the_template_directory(): void
    {
        $this->compiler->setFileName('/var/www/resources/views/users/index.blade.php');

        $this->assertSame(
            '/** file: users/index.blade.php, line: 1 */{{ $foo }}',
            $this->compiler->compileString('{{ $foo }}')
        );
    }

    /**
     * @phpstan-return Generator<string, string[], mixed, mixed>
     */
    public function basicTemplateProvider(): Generator
    {
        yield 'Single line template' => ['{{ $foo }}', '/** file: foo.blade.php, line: 1 */{{ $foo }}'];
        yield 'Multi line template' => [
            <<<'TEMPLATE'
<h1>
  {{ $foo }}
</h1>
TEMPLATE
,
            <<<'TEMPLATE'
/** file: foo.blade.php, line: 1 */<h1>
/** file: foo.blade.php, line: 2 */  {{ $foo }}
/** file: foo.blade.php, line: 3 */</h1>
TEMPLATE,
        ];
    }
}
