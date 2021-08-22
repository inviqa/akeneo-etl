<?php

namespace AkeneoE3\Infrastructure\Extractor;

use Akeneo\Pim\ApiClient\Search\SearchBuilder;
use AkeneoE3\Domain\AkeneoSpecifics;
use AkeneoE3\Domain\Profile\ExtractProfile;
use LogicException;

class Query
{
    private array $requiredValues = [];

    private array $searchFilters = [];

    public function __construct(ExtractProfile $profile, string $resourceType)
    {
        $indexedConditions = [];

        foreach ($profile->getConditions() as $condition) {
            $fieldName = $condition['field'];
            $indexedConditions[$fieldName] = $condition;
        }

        $requiredFieldNames = AkeneoSpecifics::getQueryFields($resourceType);

        foreach ($requiredFieldNames as $requiredFieldName) {
            if (isset($indexedConditions[$requiredFieldName]) === false) {
                throw new LogicException(sprintf('% field is required for %s', $requiredFieldName, $resourceType));
            }
            if (isset($indexedConditions[$requiredFieldName]['value']) === false) {
                throw new LogicException(sprintf('% field must have a value', $requiredFieldName));
            }

            $this->requiredValues[$requiredFieldName] = $indexedConditions[$requiredFieldName]['value'];

            unset($indexedConditions[$requiredFieldName]);
        }

        $builder = new SearchBuilder();

        foreach ($indexedConditions as $fieldName => $condition) {
            $value = $condition['value'] ?? null;
            $operator = $condition['operator'] ?? '=';
            $builder->addFilter((string)$fieldName, $operator, $value);
        }

        if (count($profile->getDryRunCodes()) > 0) {
            $codeFieldName = AkeneoSpecifics::getCodeFieldName($resourceType);
            $builder->addFilter($codeFieldName, 'IN', $profile->getDryRunCodes());
        }

        $this->searchFilters = ['search' => $builder->getFilters()];
    }

    public static function fromProfile(ExtractProfile $profile, string $resourceType): self
    {
        return new self($profile, $resourceType);
    }

    public function getSearchFilters(): array
    {
        return $this->searchFilters;
    }

    /**
     * @return mixed
     */
    public function getValue(string $field)
    {
        return $this->requiredValues[$field];
    }
}
