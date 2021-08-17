<?php

namespace AkeneoEtl\Tests\Shared;

use AkeneoEtl\Application\Expression\FunctionProvider;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionFunction;

require_once('src/Application/Expression/Functions/functions.php');

class FunctionDocumentor
{
    private FunctionProvider $functionProvider;

    public function __construct(FunctionProvider $functionProvider)
    {
        $this->functionProvider = $functionProvider;
    }

    public function getFunctions(): array
    {
        $result = [];

        $factory = DocBlockFactory::createInstance();


        foreach ($this->functionProvider->getFunctions() as $function) {
            $name = $function->getName();

            $refFunction = new ReflectionFunction(FunctionProvider::EXPRESSION_FUNCTIONS_NAMESPACE.$name);

            $comment = $refFunction->getDocComment();

            if ($comment === false) {
                continue;
            }

            $docblock = $factory->create($comment);

            $parameters = [];
            $examples = [];

            $tags = $docblock->getTags();

            foreach ($tags as $tag) {
                if ($tag instanceof Param) {
                    $parameters[$tag->getVariableName()] = [
                        'name' => $tag->getVariableName(),
                        'type' => $tag->getType() ?? '',
                        'description' => $tag->getDescription() ?? '',
                    ];
                }

                if ($tag instanceof Generic && $tag->getName() === 'meta-arguments') {
                    $content = $tag->getDescription();

                    $arguments = str_getcsv($content);
                    $invokeResult = $refFunction->invokeArgs($arguments);

                    $examples[] = [
                        'arguments' => $content,
                        'result' => $invokeResult,
                    ];
                }
            }

            $result[$name] = [
                'summary' => $docblock->getSummary(),
                'description' => (string)$docblock->getDescription(),
                'parameters' => $parameters,
                'examples' => $examples,
            ];
        }

        return $result;
    }
}
