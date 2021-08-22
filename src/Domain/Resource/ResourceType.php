<?php

namespace AkeneoE3\Domain\Resource;

use Stringable;

class ResourceType implements Stringable
{
    private string $code;

    public function __construct(string $code)
    {
        $this->code = $code;
    }

    public static function create(string $code): self
    {
        return new self($code);
    }

    public function __toString(): string
    {
        return $this->code;
    }

    public function getCodeFieldName(): string
    {
        return $this->code !== 'product' ? 'code' : 'identifier';
    }

    public function getChannelFieldName(): string
    {
        return $this->code === 'reference-entity-record' ? 'channel' : 'scope';
    }

    public function getQueryFields(): array
    {
        switch ($this->code) {
            case 'reference-entity-record':
                return ['reference_entity_code'];
        }

        return [];
    }
}
