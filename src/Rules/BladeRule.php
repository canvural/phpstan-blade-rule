<?php

declare(strict_types=1);

namespace Vural\PHPStanBladeRule\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\DirectRegistry;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use Vural\PHPStanBladeRule\NodeAnalyzer\BladeViewMethodsMatcher;
use Vural\PHPStanBladeRule\NodeAnalyzer\LaravelViewFunctionMatcher;

/** @implements Rule<Node> */
class BladeRule implements Rule
{
    /**
     * @param Rule[] $rules
     * @phpstan-param Rule<Node>[] $rules
     */
    public function __construct(
        array $rules,
        private BladeViewMethodsMatcher $bladeViewMethodsMatcher,
        private LaravelViewFunctionMatcher $laravelViewFunctionMatcher,
        private ViewRuleHelper $ruleHelper
    ) {
        $this->ruleHelper->setRegistry(new DirectRegistry($rules));
    }

    public function getNodeType(): string
    {
        return Node::class;
    }

    /** @inheritDoc */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($node instanceof Node\Expr\FuncCall) {
            return $this->processLaravelViewFunction($node, $scope);
        }

        if ($node instanceof Node\Expr\MethodCall) {
            return $this->processBladeView($node, $scope);
        }

        return [];
    }

    /** @return RuleError[] */
    private function processLaravelViewFunction(Node\Expr\FuncCall $node, Scope $scope): array
    {
        $renderTemplatesWithParameters = $this->laravelViewFunctionMatcher->match($node, $scope);

        return $this->ruleHelper->processNode($node, $scope, $renderTemplatesWithParameters);
    }

    /** @return RuleError[] */
    private function processBladeView(Node\Expr\MethodCall $node, Scope $scope): array
    {
        $renderTemplatesWithParameters = $this->bladeViewMethodsMatcher->match($node, $scope);

        return $this->ruleHelper->processNode($node, $scope, $renderTemplatesWithParameters);
    }
}
