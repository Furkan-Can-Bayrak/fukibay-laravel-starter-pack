<?php

namespace Fukibay\StarterPack\Traits;

use Fukibay\StarterPack\Criteria\QueryParameters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait AppliesQueryCriteria
{
    protected function applyCriteria(?QueryParameters $criteria = null): Builder
    {
        $query = $this->model->newQuery();

        if (!$criteria) {
            return $query;
        }


        if ($criteria->withoutAllScopes) {
            $query->withoutGlobalScopes();
        } elseif (!empty($criteria->withoutScopes)) {
            $query->withoutGlobalScopes($criteria->withoutScopes);
        }

        if (!empty($criteria->scopes)) {
            foreach ($criteria->scopes as $scope => $parameters) {
                if (is_int($scope)) { $scopeName = $parameters; $params = []; }
                else { $scopeName = $scope; $params = (array)$parameters; }

                $studly = Str::studly($scopeName);
                if (method_exists($this->model, 'scope'.$studly)) {
                    $query->{Str::camel($scopeName)}(...$params);
                }
            }
        }


        if ($criteria->relations) {
            $query->with($criteria->relations);
        }

        if ($criteria->relationFilters) {
            foreach ($criteria->relationFilters as $relPath => $rules) {
                $this->applyRelFilterGroup($query, (string) $relPath, (array) $rules);
            }
        }

        if ($criteria->filters) {
            foreach ($criteria->filters as $field => $value) {
                $this->applyFilter($query, (string) $field, $value);
            }
        }

        if ($criteria->orderBy) {
            foreach ($criteria->orderBy as $col => $dir) {
                $dir = strtolower((string)$dir);
                $dir = $dir === 'desc' ? 'desc' : 'asc'; //sql enjeksiyonu olmasın diye güvenlik eklendi
                $query->orderBy((string)$col, $dir);
            }
        }


        if (is_int($criteria->limit) && $criteria->limit > 0) {
            $query->limit($criteria->limit);
        }

        return $query;
    }

    protected function applyRelFilterGroup(Builder $query, string $relPath, array $rules): void
    {
        foreach ($rules as $col => $rule) {
            if (!is_array($rule)) {
                $query->whereRelation($relPath, (string) $col, '=', $rule);
                continue;
            }

            $op  = strtolower((string) ($rule[0] ?? '='));
            $val = $rule[1] ?? null;

            if (in_array($op, ['=', '!=', '>', '>=', '<', '<='], true)) {
                $query->whereRelation($relPath, (string) $col, $op === '!=' ? '<>' : $op, $val);
                continue;
            }

            if ($op === 'like') {
                $val = is_string($val) && !str_contains($val, '%') ? "%{$val}%" : $val;
                $query->whereRelation($relPath, (string) $col, 'like', $val);
                continue;
            }

            if ($op === 'date') {
                $query->whereHas($relPath, function (Builder $qr) use ($col, $val) {
                    $qr->whereDate((string) $col, $val);
                });
                continue;
            }
        }
    }

    protected function applyFilter(Builder $query, string $field, mixed $value): void
    {
        if ($field === 'exists' || $field === 'not_exists') {
            foreach ((array) $value as $relation) {
                if ($field === 'exists') {
                    $query->whereHas((string) $relation);
                } else {
                    $query->whereDoesntHave((string) $relation);
                }
            }
            return;
        }

        if (str_contains($field, '.')) {
            $lastDot = strrpos($field, '.');
            $relPath = substr($field, 0, $lastDot);
            $col     = substr($field, $lastDot + 1);

            if (!is_array($value)) {
                $query->whereRelation($relPath, $col, '=', $value);
                return;
            }

            $op  = strtolower((string) ($value[0] ?? '='));
            $val = $value[1] ?? null;

            if (in_array($op, ['=', '!=', '>', '>=', '<', '<='], true)) {
                $query->whereRelation($relPath, $col, $op === '!=' ? '<>' : $op, $val);
                return;
            }

            if ($op === 'like') {
                $val = is_string($val) && !str_contains($val, '%') ? "%{$val}%" : $val;
                $query->whereRelation($relPath, $col, 'like', $val);
                return;
            }

            if ($op === 'date') {
                $query->whereHas($relPath, function (Builder $qr) use ($col, $val) {
                    $qr->whereDate($col, $val);
                });
                return;
            }

            return;
        }

        if (!is_array($value)) {
            $query->where($field, $value);
            return;
        }

        $op  = strtolower((string) ($value[0] ?? '='));
        $val = $value[1] ?? null;

        switch ($op) {
            case '=':
            case '!=':
            case '>':
            case '>=':
            case '<':
            case '<=':
                $query->where($field, $op === '!=' ? '<>' : $op, $val);
                break;
            case 'like':
                $val = is_string($val) && !str_contains($val, '%') ? "%{$val}%" : $val;
                $query->where($field, 'like', $val);
                break;
            case 'in':
                $query->whereIn($field, (array) $val);
                break;
            case 'between':
                [$from, $to] = (array) $val;
                $query->whereBetween($field, [$from, $to]);
                break;
            case 'null':
                $query->whereNull($field);
                break;
            case 'not_null':
                $query->whereNotNull($field);
                break;
            case 'date':
                $query->whereDate($field, $val);
                break;
            default:
                $query->where($field, $val);
        }
    }
}
