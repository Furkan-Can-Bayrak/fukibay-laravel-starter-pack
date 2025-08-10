<?php

namespace {{ namespace }}\Services;

use {{ namespace }}\Repositories\Contracts\BaseRepositoryInterface;
use {{ namespace }}\Repositories\Criteria\QueryParameters;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Controller'ların konuşacağı ortak servis katmanı.
 * Tüm işlemleri ilgili repository'e delege eder.
 * Yazma işlemleri (create/update/delete) transaction içinde yapılır.
 */
abstract class BaseService
{
    public function __construct(
        protected BaseRepositoryInterface $repository
    ) {}

    public function all(): Collection
    {
        return $this->repository->all();
    }

    public function get(?QueryParameters $criteria = null): Collection
    {
        return $this->repository->get($criteria);
    }

    public function paginate(
        ?QueryParameters $criteria = null,
        int $perPage = 15,
        string $pageName = 'page',
        ?int $page = null
    ): LengthAwarePaginator {
        return $this->repository->paginate($criteria, $perPage, $pageName, $page);
    }

    public function findBy(?QueryParameters $criteria = null): ?Model
    {
        return $this->repository->findBy($criteria);
    }

    public function findByOrFail(?QueryParameters $criteria = null): Model
    {
        return $this->repository->findByOrFail($criteria);
    }

    public function findById(int $id, array $relations = [], array $columns = ['*']): ?Model
    {
        return $this->repository->findById($id, $relations, $columns);
    }

    public function findByIdOrFail(int $id, array $relations = [], array $columns = ['*']): Model
    {
        return $this->repository->findByIdOrFail($id, $relations, $columns);
    }

    public function create(array $data): Model
    {
        return DB::transaction(fn () => $this->repository->create($data));
    }

    public function update(int $id, array $data): Model
    {
        return DB::transaction(fn () => $this->repository->update($id, $data));
    }

    public function delete(int $id): void
    {
        DB::transaction(fn () => $this->repository->delete($id));
    }
}
