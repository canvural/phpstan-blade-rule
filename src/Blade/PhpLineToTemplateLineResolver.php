<?php

declare(strict_types=1);

namespace Vural\PHPStanBladeRule\Blade;

use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use Vural\PHPStanBladeRule\PHPParser\NodeVisitor\BladeLineNumberNodeVisitor;

final class PhpLineToTemplateLineResolver
{
    private Parser $parser;

    public function __construct(private BladeLineNumberNodeVisitor $bladeLineNumberNodeVisitor)
    {
        $parserFactory = new ParserFactory();
        $this->parser  = $parserFactory->create(ParserFactory::PREFER_PHP7);
    }

    /** @return array<int, array<string, int>> */
    public function resolve(string $phpFileContent): array
    {
        $stmts = $this->parser->parse($phpFileContent);

        if ($stmts === [] || $stmts === null) {
            return [];
        }

        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($this->bladeLineNumberNodeVisitor);
        $nodeTraverser->traverse($stmts);

        return $this->bladeLineNumberNodeVisitor->getPhpLineToBladeTemplateLineMap();
    }
}
