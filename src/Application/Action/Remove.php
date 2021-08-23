<?php

declare(strict_types=1);

namespace AkeneoE3\Application\Action;

use AkeneoE3\Application\Expression\ActionState;
use AkeneoE3\Application\Expression\ExpressionLanguage;
use AkeneoE3\Domain\Action;
use AkeneoE3\Domain\Exception\TransformException;
use AkeneoE3\Domain\Resource\Field;
use AkeneoE3\Domain\Resource\FieldFactory;
use AkeneoE3\Domain\Resource\Resource;

final class Remove implements Action
{
    private Field $field;

    private AddRemoveOptions $options;

    private ExpressionLanguage $expressionLanguage;

    public function __construct(ExpressionLanguage $expressionLanguage, array $options)
    {
        $this->expressionLanguage = $expressionLanguage;
        $this->field = FieldFactory::fromOptions($options);
        $this->options = AddRemoveOptions::fromArray($options);
    }

    public function execute(Resource $resource): void
    {
        $resultValue = $this->evaluateValue($resource);

        $resource->removeFrom($this->field, $resultValue);
    }

    protected function evaluateValue(Resource $resource): array
    {
        if ($this->options->getExpression() === null) {
            return $this->options->getItems();
        }

        ActionState::setResourceAndField($resource, $this->field);

        $expression = $this->options->getExpression() ?? '';
        $result = $this->expressionLanguage->evaluate($expression, $resource->toArray(true));

        if (is_array($result) === false) {
            throw new TransformException(sprintf('Expected result type of the expression %s is array', $expression), true);
        }

        return $result;
    }
}
