<?php

declare(strict_types=1);

namespace Vural\PHPStanBladeRule\Compiler;

use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\FileViewFinder;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Throwable;
use Vural\PHPStanBladeRule\ValueObject\BladeInclude;

use function count;
use function implode;
use function rtrim;
use function sprintf;
use function ucfirst;

use const PHP_EOL;

final class IncludeCompiler
{
    private Parser $parser;

    public function __construct(
        private BladeCompiler $compiler,
        private Standard $printerStandard,
        private FileViewFinder $fileViewFinder,
        private Filesystem $fileSystem,
        private FileNameAndLineNumberAddingPreCompiler $preCompiler,
        private PhpContentExtractor $phpContentExtractor,
    ) {
        $parserFactory = new ParserFactory();
        $this->parser  = $parserFactory->create(ParserFactory::PREFER_PHP7);
    }

    public function decorateInclude(BladeInclude $include): string
    {
        try {
            $includedFilePath     = $this->fileViewFinder->find($include->name);
            $includedFileContents = $this->fileSystem->get($includedFilePath);

            $preCompiledContents = $this->preCompiler->setFileName($includedFilePath)->compileString($includedFileContents);
            $compiledContent     = $this->compiler->compileString($preCompiledContents);
            $includedContent     = $this->phpContentExtractor->extract(
                $compiledContent,
                false
            );

            if (! $include->variables) {
                return $includedContent;
            }

            $expressions = $this->parser->parse('<?php ' . rtrim($include->variables, ',') . ';');

            if (! $expressions || count($expressions) !== 1) {
                return '';
            }

            if (! ($expressions[0] instanceof Expression)) {
                return '';
            }

            $array = $expressions[0]->expr;
            if (! ($array instanceof Array_)) {
                return '';
            }

            $variables = $array->items;

            $variablesDefinitions = [];
            $variablesReseting    = [];
            foreach ($variables as $arrayItem) {
                if (! $arrayItem) {
                    continue;
                }

                if (! ($arrayItem->key instanceof String_)) {
                    continue;
                }

                $variableName          = $arrayItem->key->value;
                $temporaryVariableName = '__previous' . ucfirst($variableName);
                $phpExpression         = $this->printerStandard->prettyPrintExpr($arrayItem->value);

                $variablesDefinitions[] = sprintf('if (isset($%s)) { $%s = $%s; }', $variableName, $temporaryVariableName, $variableName);
                $variablesDefinitions[] = sprintf('$%s = %s;', $variableName, $phpExpression);

                $variablesReseting[] = sprintf('if (isset($%s)) { $%s = $%s; }', $temporaryVariableName, $variableName, $temporaryVariableName);
            }

            return implode(PHP_EOL, $variablesDefinitions) . PHP_EOL . $includedContent . PHP_EOL . implode(PHP_EOL, $variablesReseting);
        } catch (Throwable) {
            return '';
        }
    }
}
