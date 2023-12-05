<?php

namespace App\Modules\Core\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class ItemsRepository extends BaseRepository
{
    protected Builder $baseQuery;

    public function pagination(Collection $options): LengthAwarePaginator
    {
        $query = $this->buildIndexQuery($options);

        return $query->paginate(
            $options->get('per_page', 15),
            ['*'],
            'page',
            $options->get('page', 1)
        );
    }

    public function items(?Collection $options = null): Collection
    {
        return $this->buildIndexQuery($options ?? collect())->get();
    }

    private function buildIndexQuery(Collection $options): Builder
    {
        /**
         * `source` is Query Builder instance
         * `model` is Eloquent Model instance
         */
        if ($options->has('model')) {
            $this->baseQuery = $options['model']->newQuery();
        } elseif ($options->has('source')) {
            $this->baseQuery = $options['source'];
        } else {
            $this->baseQuery = $this->getModel()->newQuery();
        }

        $this->ordering($options);

        $queries = [
            'whereAfter',
            'where',
            'whereIn',
            'whereNull',
            'whereNotNull',
            'whereNotEmpty',
            'whereHas',
            'whereHasIn',
            'whereDoesntHave',
            'has',
            'doesntHave',
            'search',
            'addSelect',
            'groupBy'
        ];

        foreach ($queries as $query) {
            if (!$options->has($query) || !$options->get($query)) {
                continue;
            }

            $this->{$query}($options);
        }

        /**
         * Relationships
         */

        $options['with'] = array_merge(
            $options->get('with', []),
            $options['with'] ?? []
        );

        if (isset($options['with'])) {
            foreach ($options['with'] as $relation) {
                $this->baseQuery->with($relation);
            }
        }

        return $this->baseQuery;
    }

    private function ordering(Collection $options): void
    {
        $this->baseQuery->orderBy(
            $options->get('orderBy', 'created_at'),
            $options->get('orderDirection', 'desc')
        );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function whereAfter(Collection $options): void
    {
        if (!is_array($options->get('whereAfter')) || empty($options->get('whereAfter'))) {
            return;
        }

        foreach ($options->get('whereAfter') as $key => $values) {
            $this->baseQuery->where(function ($query) use ($key, $values) {
                $query->where($key, '<=', $values)
                    ->orWhereNull($key);
            });
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function where(Collection $options): void
    {
        if (is_array($options->get('where'))) {
            foreach ($options->get('where') as $key => $values) {
                $this->baseQuery->where($key, $values);
            }

            return;
        }

        $this->baseQuery->where($options->get('where'));
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function whereIn(Collection $options): void
    {
        if (!is_array($options->get('whereIn')) || empty($options->get('whereIn'))) {
            return;
        }

        foreach ($options->get('whereIn') as $key => $values) {
            if (empty($values)) {
                continue;
            }
            $this->baseQuery->whereIn($key, $values);
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function whereNull(Collection $options)
    {
        if (!is_array($options->get('whereNull')) || empty($options->get('whereNull'))) {
            return;
        }

        foreach ($options->get('whereNull') as $column) {
            $this->baseQuery->whereNull($column);
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function whereNotNull(Collection $options)
    {
        if (!is_array($options->get('whereNotNull')) || empty($options->get('whereNotNull'))) {
            return;
        }

        foreach ($options->get('whereNotNull') as $column) {
            $this->baseQuery->whereNotNull($column);
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function whereNotEmpty(Collection $options): void
    {
        if (!is_array($options->get('whereNotEmpty')) || empty($options->get('whereNotEmpty'))) {
            return;
        }

        foreach ($options->get('whereNotEmpty') as $column) {
            $this->baseQuery->where($column, '<>', '');
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function whereHas(Collection $options): void
    {
        if (!is_array($options->get('whereHas')) || empty($options->get('whereHas'))) {
            return;
        }

        foreach ($options->get('whereHas') as $relationship => $conditions) {
            foreach ($conditions as $where) {
                $this->baseQuery->whereHas($relationship, function ($query) use ($where) {
                    $query->where($where);
                });
            }
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function whereHasIn(Collection $options): void
    {
        if (!is_array($options->get('whereHasIn')) || empty($options->get('whereHasIn'))) {
            return;
        }

        foreach ($options->get('whereHasIn') as $relationship => $where) {
            $this->baseQuery->whereHas($relationship, function ($query) use ($where) {
                foreach ($where as $field => $value) {
                    $query->whereIn($field, $value);
                }
            });
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function whereDoesntHave(Collection $options): void
    {
        if (!is_array($options->get('whereDoesntHave')) || empty($options->get('whereHasIn'))) {
            return;
        }

        foreach ($options->get('whereDoesntHave') as $relationship => $where) {
            $this->baseQuery->whereDoesntHave($relationship, function ($query) use ($where) {
                $query->where($where);
            });
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function has(Collection $options): void
    {
        if (!is_array($options->get('has')) || empty($options->get('has'))) {
            return;
        }

        foreach ($options->get('has') as $relationship) {
            $this->baseQuery->has($relationship);
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function doesntHave(Collection $options)
    {
        if (!is_array($options->get('doesntHave')) || empty($options->get('doesntHave'))) {
            return;
        }

        foreach ($options->get('doesntHave') as $relationship) {
            $this->baseQuery->doesntHave($relationship);
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function search(Collection $options): void
    {
        if (!$options->has('searchIn') || !$options->get('searchIn')) {
            return;
        }

        $this->baseQuery->where(function ($query) use ($options) {
            foreach ($options->get('searchIn') as $key => $columns) {
                if (!is_array($columns)) {
                    $query->orWhere($columns, 'like', '%' . $options->get('search') . '%');
                    continue;
                }

                $query->orWhereHas($key, function ($query) use ($options, $columns) {
                    $query->where(function ($query) use ($options, $columns) {
                        foreach ($columns as $column) {
                            $query->orWhere($column, 'like', '%' . $options->get('search') . '%');
                        }
                    });
                });
            }
        });
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function addSelect(Collection $options): void
    {
        if (!$options->has('addSelect') || !$options->get('addSelect')) {
            return;
        }

        foreach ((array)$options->get('addSelect') as $addSelect) {
            $this->baseQuery->addSelect($addSelect);
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function groupBy(Collection $options): void
    {
        $groupsBy = $options->get('groupBy');

        if (empty($groupsBy)) {
            return;
        }

        if (is_array($groupsBy)) {
            foreach ($groupsBy as $groupBy) {
                $this->baseQuery->groupBy($groupBy);
            }

            return;
        }

        $this->baseQuery->groupBy($groupsBy);
    }
}
