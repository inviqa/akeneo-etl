<?php

namespace AkeneoE3\Infrastructure\Loader;

use AkeneoE3\Domain\Repository\WriteRepository;
use AkeneoE3\Domain\Resource\Resource;
use AkeneoE3\Domain\Resource\ResourceCollection;

class ApiBatchLoader implements WriteRepository
{
    private WriteResourcesRepository $connector;

    private ResourceCollection $buffer;

    private int $batchSize;

    public function __construct(WriteResourcesRepository $connector, int $batchSize)
    {
        $this->connector = $connector;
        $this->batchSize = $batchSize;

        $this->buffer = new ResourceCollection();
    }

    public function persist(Resource $resource, bool $patch): iterable
    {
        $this->buffer->add($resource);

        if ($this->buffer->count() === $this->batchSize) {
            yield from $this->flush($patch);
        }

        return [];
    }

    public function flush(bool $patch): iterable
    {
        yield from $this->connector->write($this->buffer, $patch);

        $this->buffer->clear();
    }
}
