<?php
declare(strict_types=1);

namespace Fukibay\StarterPack\Traits;

use Fukibay\StarterPack\Criteria\QueryParameters;
use Fukibay\StarterPack\Repositories\Contracts\SoftDeletesRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * Servis katmanından soft delete yeteneklerini "proxy" eder.
 * $this->repository SoftDeletesRepositoryInterface değilse açık hata fırlatır.
 *
 * Kullanım:
 * class UserService extends BaseService { use ProxiesSoftDeletes; }
 */
trait ProxiesSoftDeletes
{
    protected function softRepo(): SoftDeletesRepositoryInterface
    {
        if (!($this->repository instanceof SoftDeletesRepositoryInterface)) {
            throw new LogicException('Repository soft deletes desteklemiyor.');
        }
        return $this->repository;
    }

    /* ===== Listeleme: Aktif + Silinmiş ===== */

    public function withTrashed(?QueryParameters $criteria = null): Collection
    {
        return $this->softRepo()->withTrashed($criteria);
    }

    public function withTrashedPaginate(
        ?QueryParameters $criteria = null,
        int $perPage = 15,
        string $pageName = 'page',
        ?int $page = null
    ): LengthAwarePaginator {
        return $this->softRepo()->withTrashedPaginate($criteria, $perPage, $pageName, $page);
    }

    /* ===== Listeleme: Sadece Silinmiş ===== */

    public function onlyTrashed(?QueryParameters $criteria = null): Collection
    {
        return $this->softRepo()->onlyTrashed($criteria);
    }

    public function onlyTrashedPaginate(
        ?QueryParameters $criteria = null,
        int $perPage = 15,
        string $pageName = 'page',
        ?int $page = null
    ): LengthAwarePaginator {
        return $this->softRepo()->onlyTrashedPaginate($criteria, $perPage, $pageName, $page);
    }

    /* ===== Bulma (criteria / id) ===== */

    public function findWithTrashedBy(?QueryParameters $criteria = null): ?Model
    {
        return $this->softRepo()->findWithTrashedBy($criteria);
    }

    public function findWithTrashedByOrFail(?QueryParameters $criteria = null): Model
    {
        return $this->softRepo()->findWithTrashedByOrFail($criteria);
    }

    public function findOnlyTrashedBy(?QueryParameters $criteria = null): ?Model
    {
        return $this->softRepo()->findOnlyTrashedBy($criteria);
    }

    public function findOnlyTrashedByOrFail(?QueryParameters $criteria = null): Model
    {
        return $this->softRepo()->findOnlyTrashedByOrFail($criteria);
    }

    public function findWithTrashedById(int $id, array $relations = [], array $columns = ['*']): ?Model
    {
        return $this->softRepo()->findWithTrashedById($id, $relations, $columns);
    }

    public function findWithTrashedByIdOrFail(int $id, array $relations = [], array $columns = ['*']): Model
    {
        return $this->softRepo()->findWithTrashedByIdOrFail($id, $relations, $columns);
    }

    public function findOnlyTrashedById(int $id, array $relations = [], array $columns = ['*']): ?Model
    {
        return $this->softRepo()->findOnlyTrashedById($id, $relations, $columns);
    }

    public function findOnlyTrashedByIdOrFail(int $id, array $relations = [], array $columns = ['*']): Model
    {
        return $this->softRepo()->findOnlyTrashedByIdOrFail($id, $relations, $columns);
    }

    /* ===== İşlemler ===== */

    public function restore(int $id): bool
    {
        return $this->softRepo()->restore($id);
    }

    public function forceDelete(int $id): bool
    {
        return $this->softRepo()->forceDelete($id);
    }
}
