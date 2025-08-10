<?php

namespace Fukibay\StarterPack\Traits;

use Fukibay\StarterPack\Criteria\QueryParameters;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\SoftDeletes;
use LogicException;

/**
 * SoftDeletesRepositoryInterface metotlarının default implementasyonu.
 *
 * Kullanım:
 * class UserRepository extends BaseRepository implements SoftDeletesRepositoryInterface {
 *     use HandlesSoftDeletes;
 * }
 *
 * Gerekenler:
 * - $this->model : \Illuminate\Database\Eloquent\Model (BaseRepository'den)
 * - applyCriteria(?QueryParameters $criteria = null): \Illuminate\Database\Eloquent\Builder
 */
trait HandlesSoftDeletes
{
    /* ===================== Listeleme: Aktif + Silinmiş Birlikte ===================== */

    /**
     * @inheritDoc
     */
    public function withTrashed(?QueryParameters $criteria = null): Collection
    {
        $this->assertSupportsSoftDeletes();
        $query = $this->applyCriteria($criteria);
        return $query->withTrashed()->get($criteria?->columns ?? ['*']);
    }

    /**
     * @inheritDoc
     */
    public function withTrashedPaginate(
        ?QueryParameters $criteria = null,
        int $perPage = 15,
        string $pageName = 'page',
        ?int $page = null
    ): LengthAwarePaginator {
        $this->assertSupportsSoftDeletes();
        $query = $this->applyCriteria($criteria);
        return $query->withTrashed()->paginate($perPage, $criteria?->columns ?? ['*'], $pageName, $page);
    }

    /* ===================== Listeleme: Sadece Silinmiş ===================== */

    /**
     * @inheritDoc
     */
    public function onlyTrashed(?QueryParameters $criteria = null): Collection
    {
        $this->assertSupportsSoftDeletes();
        $query = $this->applyCriteria($criteria);
        return $query->onlyTrashed()->get($criteria?->columns ?? ['*']);
    }

    /**
     * @inheritDoc
     */
    public function onlyTrashedPaginate(
        ?QueryParameters $criteria = null,
        int $perPage = 15,
        string $pageName = 'page',
        ?int $page = null
    ): LengthAwarePaginator {
        $this->assertSupportsSoftDeletes();
        $query = $this->applyCriteria($criteria);
        return $query->onlyTrashed()->paginate($perPage, $criteria?->columns ?? ['*'], $pageName, $page);
    }

    /* ===================== Bulma: Aktif + Silinmiş Birlikte ===================== */

    /**
     * @inheritDoc
     */
    public function findWithTrashedBy(?QueryParameters $criteria = null): ?Model
    {
        $this->assertSupportsSoftDeletes();
        $query = $this->applyCriteria($criteria);
        return $query->withTrashed()->first($criteria?->columns ?? ['*']);
    }

    /**
     * @inheritDoc
     * @throws ModelNotFoundException
     */
    public function findWithTrashedByOrFail(?QueryParameters $criteria = null): Model
    {
        $this->assertSupportsSoftDeletes();
        $query = $this->applyCriteria($criteria);
        return $query->withTrashed()->firstOrFail($criteria?->columns ?? ['*']);
    }

    /**
     * @inheritDoc
     */
    public function findWithTrashedById(int $id, array $relations = [], array $columns = ['*']): ?Model
    {
        $this->assertSupportsSoftDeletes();
        return $this->model->withTrashed()->with($relations)->find($id, $columns);
    }

    /**
     * @inheritDoc
     * @throws ModelNotFoundException
     */
    public function findWithTrashedByIdOrFail(int $id, array $relations = [], array $columns = ['*']): Model
    {
        $this->assertSupportsSoftDeletes();
        return $this->model->withTrashed()->with($relations)->findOrFail($id, $columns);
    }

    /* ===================== Bulma: Sadece Silinmiş ===================== */

    /**
     * @inheritDoc
     */
    public function findOnlyTrashedBy(?QueryParameters $criteria = null): ?Model
    {
        $this->assertSupportsSoftDeletes();
        $query = $this->applyCriteria($criteria);
        return $query->onlyTrashed()->first($criteria?->columns ?? ['*']);
    }

    /**
     * @inheritDoc
     * @throws ModelNotFoundException
     */
    public function findOnlyTrashedByOrFail(?QueryParameters $criteria = null): Model
    {
        $this->assertSupportsSoftDeletes();
        $query = $this->applyCriteria($criteria);
        return $query->onlyTrashed()->firstOrFail($criteria?->columns ?? ['*']);
    }

    /**
     * @inheritDoc
     */
    public function findOnlyTrashedById(int $id, array $relations = [], array $columns = ['*']): ?Model
    {
        $this->assertSupportsSoftDeletes();
        return $this->model->onlyTrashed()->with($relations)->find($id, $columns);
    }

    /**
     * @inheritDoc
     * @throws ModelNotFoundException
     */
    public function findOnlyTrashedByIdOrFail(int $id, array $relations = [], array $columns = ['*']): Model
    {
        $this->assertSupportsSoftDeletes();
        return $this->model->onlyTrashed()->with($relations)->findOrFail($id, $columns);
    }

    /* ===================== İşlemler ===================== */

    /**
     * @inheritDoc
     * @throws ModelNotFoundException
     */
    public function restore(int $id): bool
    {
        $this->assertSupportsSoftDeletes();
        $record = $this->findOnlyTrashedByIdOrFail($id);
        return (bool) $record->restore();
    }

    /**
     * @inheritDoc
     * @throws ModelNotFoundException
     */
    public function forceDelete(int $id): bool
    {
        $this->assertSupportsSoftDeletes();
        $record = $this->findWithTrashedByIdOrFail($id);
        return (bool) $record->forceDelete();
    }

    /* ===================== Destek metotları ===================== */

    /**
     * Modelin SoftDeletes kullandığını doğrular.
     */
    protected function assertSupportsSoftDeletes(): void
    {
        $uses = class_uses_recursive($this->model);
        if (!in_array(SoftDeletes::class, $uses, true)) {
            $class = get_class($this->model);
            throw new LogicException("{$class} SoftDeletes kullanmıyor; soft-delete işlemleri desteklenmiyor.");
        }
    }

    /**
     * BaseRepository tarafından sağlanmalı.
     *
     * @param QueryParameters|null $criteria
     * @return \Illuminate\Database\Eloquent\Builder
     */
    abstract protected function applyCriteria(?QueryParameters $criteria = null);
}
