<?php

namespace AkeneoE3\Infrastructure\Api;

use AkeneoE3\Domain\Repository\Query;
use AkeneoE3\Domain\Repository\ReadRepository;
use AkeneoE3\Domain\Resource\Resource;
use AkeneoE3\Infrastructure\Api\Query\ApiQuery;
use AkeneoE3\Infrastructure\Api\Repository\ReadResourcesRepository;
use LogicException;

class ApiReadRepository implements ReadRepository
{
    private ReadResourcesRepository $repository;

    public function __construct(ReadResourcesRepository $repository)
    {
        $this->repository = $repository;
    }

    public function count(Query $query): int
    {
        if (!$query instanceof ApiQuery) {
            throw new LogicException('Query must be of type ApiQuery');
        }

        return $this->repository->count($query);
    }

    /**
     * @return iterable<Resource>
     */
    public function read(Query $query): iterable
    {
        if (!$query instanceof ApiQuery) {
            throw new LogicException('Query must be of type ApiQuery');
        }

        return $this->repository->read($query);
    }
}