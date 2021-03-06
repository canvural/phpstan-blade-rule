<?php

declare(strict_types=1);

namespace Vural\PHPStanBladeRule\Tests\Blade;

use Generator;
use PHPUnit\Framework\TestCase;
use Vural\PHPStanBladeRule\Blade\PhpLineToTemplateLineResolver;
use Vural\PHPStanBladeRule\PHPParser\NodeVisitor\BladeLineNumberNodeVisitor;

/**
 * @covers \Vural\PHPStanBladeRule\Blade\PhpLineToTemplateLineResolver
 * @covers \Vural\PHPStanBladeRule\PHPParser\NodeVisitor\BladeLineNumberNodeVisitor
 */
class PhpLineToTemplateLineResolverTest extends TestCase
{
    private PhpLineToTemplateLineResolver $phpLineToTemplateLineResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->phpLineToTemplateLineResolver = new PhpLineToTemplateLineResolver(new BladeLineNumberNodeVisitor());
    }

    /**
     * @param array<int, array<string, int>> $expected
     *
     * @test
     * @dataProvider phpContentAndLineNumberProvider
     */
    function it_can_extract_file_name_php_line_number_and_template_line_number(string $phpContent, array $expected): void
    {
        $this->assertSame($expected, $this->phpLineToTemplateLineResolver->resolve($phpContent));
    }

    /** @phpstan-return Generator<string, array{0: string, 1: array<int, array<string, int>>}, mixed, mixed> */
    public function phpContentAndLineNumberProvider(): Generator
    {
        yield 'File with no contents' => [
            '',
            [],
        ];

        yield 'File with no comments' => [
            "<?php echo 'foo';",
            [],
        ];

        yield 'File with wrong comment style' => [
            <<<'PHP'
                <?php
                // file: foo.blade.php, line: 5 */
                echo 'foo';
                /* file: foo.blade.php, line: 6 */
                echo 'foo';
PHP,
            [],
        ];

        yield 'Simple file' => [
            <<<'PHP'
                <?php
                /** file: foo.blade.php, line: 5 */
                echo 'foo';
PHP,
            [
                3 => ['foo.blade.php' => 5],
            ],
        ];

        yield 'File with multiple lines' => [
            <<<'PHP'
                <?php
                /** file: foo.blade.php, line: 5 */
                echo 'foo';
                /** file: foo.blade.php, line: 55 */
                echo 'bar';
PHP,
            [
                3 => ['foo.blade.php' => 5],
                5 => ['foo.blade.php' => 55],
            ],
        ];

        yield 'File with multiple file names' => [
            <<<'PHP'
                <?php
                /** file: foo.blade.php, line: 5 */
                echo 'foo';
                /** file: foo.blade.php, line: 6 */
                echo 'bar';
                /** file: bar.blade.php, line: 55 */
                echo 'baz';
PHP,
            [
                3 => ['foo.blade.php' => 5],
                5 => ['foo.blade.php' => 6],
                7 => ['bar.blade.php' => 55],
            ],
        ];
    }
}
