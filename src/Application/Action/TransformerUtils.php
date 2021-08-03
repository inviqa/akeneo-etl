<?php

namespace AkeneoEtl\Application\Action;

class TransformerUtils
{
    public static function getFieldValue(array $data, string $name, ?string $channel, ?string $locale): array
    {
        // @todo: check if it is a top level field first
        // @todo: check if values exist

        foreach ($data['values'][$name] ?? [] as $attributeValue) {
            if ($attributeValue['scope'] === $channel &&
                $attributeValue['locale'] === $locale) {
                return $attributeValue;
            }
        }

        return [];
    }

    /**
     * @param mixed $data
     */
    public static function createFieldArray(string $field, $data, ?string $channel, ?string $locale): array
    {
        return [
            'values' => [
                $field => [
                    ['scope' => $channel, 'locale' => $locale, 'data' => $data]
                ]
            ]
        ];
    }
}
