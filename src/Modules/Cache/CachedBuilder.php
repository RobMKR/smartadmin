<?php

/**
 * @see https://github.com/GeneaLabs/laravel-model-caching
 */

namespace MgpLabs\SmartAdmin\Modules\Cache;

use Illuminate\Cache\TaggableStore;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;

class CachedBuilder extends EloquentBuilder
{
    /**
     * Where Holder
     *
     * @var string
     */
    protected $carry = '';

    /**
     * Model Name
     *
     * @var
     */
    protected $cachable_model;

    protected function cache(array $tags = [])
    {
        $cache = cache();

        if (is_subclass_of($cache->getStore(), TaggableStore::class)) {
            $cache = $cache->tags($tags);
        }

        return $cache;
    }

    protected function getCacheKey(array $columns = ['*'], $idColumn = null)
    {
        $key = $this->getModelSlug();
        $key .= $this->getIdColumn($idColumn ?: '');
        $key .= $this->getQueryColumns($columns);
        $key .= $this->getWhereClauses();
        $key .= $this->getWithModels();
        $key .= $this->getOrderClauses();
        $key .= $this->getOffsetClause();
        $key .= $this->getLimitClause();

        return $key;
    }

    protected function getIdColumn($idColumn)
    {

        return $idColumn ? "_{$idColumn}" : '';
    }

    protected function getLimitClause()
    {
        if (! $this->query->limit) {
            return '';
        }

        return "-limit_{$this->query->limit}";
    }

    protected function getModelSlug()
    {
        return str_slug(get_class($this->model));
    }

    protected function getOffsetClause()
    {
        if (! $this->query->offset) {
            return '';
        }

        return "-offset_{$this->query->offset}";
    }

    protected function getQueryColumns(array $columns)
    {
        if ($columns === ['*'] || $columns === []) {
            return '';
        }

        return '_' . implode('_', $columns);
    }

    protected function getWhereClauses(array $wheres = [])
    {
        $wheres = collect($wheres);

        if ($wheres->isEmpty()) {
            $wheres = collect($this->query->wheres);
        }

        foreach($wheres as $where){
            if (in_array($where['type'], ['Exists', 'Nested'])) {
                $this->carry = $this->getWhereClauses($where['query']->wheres);
                continue;
            }

            if ($where['type'] === 'Column') {
                $this->carry .= "_{$where['boolean']}_{$where['first']}_{$where['operator']}_{$where['second']}";
                continue;
            }

            if ($where['type'] === 'raw') {
                $this->carry .= "_{$where['boolean']}_" . str_slug($where['sql']);
                continue;
            }

            if ($where['type'] === 'Basic') {
                $this->carry .= "_{$where['boolean']}_{$where['column']}_{$where['operator']}_{$where['value']}_";
                continue;
            }

            $value = array_get($where, 'values');

            if (in_array($where['type'], ['In', 'Null', 'NotNull'])) {
                $value = strtolower($where['type']);
            }

            if (is_array(array_get($where, 'values'))) {
                $value .= '_' . implode('_', $where['values']);
            }

            $this->carry .= "-{$where['column']}_{$value}";
        }

        return $this->carry;
    }

    protected function getWithModels()
    {
        $eagerLoads = collect($this->eagerLoad);

        if ($eagerLoads->isEmpty()) {
            return '';
        }

        return '-' . implode('-', $eagerLoads->keys()->toArray());
    }

    protected function getOrderClauses(){
        $orders = collect($this->query->orders);

        return $orders->reduce(function($carry, $order){
            $carry .= '_sort_' . array_get($order, 'column') . '_' . array_get($order, 'direction');

            return $carry;
        });
    }

    protected function getCacheTags()
    {
        return collect($this->eagerLoad)->keys()
            ->map(function ($relationName) {
                $relation = collect(explode('.', $relationName))
                    ->reduce(function ($carry, $name) {
                        if (! $carry) {
                            $carry = $this->model;
                        }

                        if ($carry instanceof Relation) {
                            $carry = $carry->getQuery()->model;
                        }

                        return $carry->{$name}();
                    });

                return str_slug(get_class($relation->getQuery()->model));
            })
            ->prepend(str_slug(get_class($this->model)))
            ->values()
            ->toArray();
    }

    public function avg($column)
    {
        $tags = [str_slug(get_class($this->model))];
        $key = str_slug(get_class($this->model)) ."-avg_{$column}";

        return $this->cache($tags)
            ->rememberForever($key, function () use ($column) {
                return parent::avg($column);
            });
    }

    public function count($columns = ['*'])
    {
        $tags = [str_slug(get_class($this->model))];
        $key = str_slug(get_class($this->model)) ."-count";

        return $this->cache($tags)
            ->rememberForever($key, function () use ($columns) {
                return parent::count($columns);
            });
    }

    public function cursor()
    {
        $tags = [str_slug(get_class($this->model))];
        $key = str_slug(get_class($this->model)) ."-cursor";

        return $this->cache($tags)
            ->rememberForever($key, function () {
                return collect(parent::cursor());
            });
    }

    /**
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function find($id, $columns = ['*'])
    {
        $tags = $this->getCacheTags();
        $key = $this->getCacheKey($columns, $id);

        return $this->cache($tags)
            ->rememberForever($key, function () use ($id, $columns) {
                return parent::find($id, $columns);
            });
    }

    public function first($columns = ['*'])
    {
        $tags = $this->getCacheTags();
        $key = $this->getCacheKey($columns) . '-first';

        return $this->cache($tags)
            ->rememberForever($key, function () use ($columns) {
                return parent::first($columns);
            });
    }

    public function get($columns = ['*'])
    {
        $this->cachable_model= $this->model;

        $tags = $this->getCacheTags();
        $key = $this->getCacheKey($columns);

        return $this->cache($tags)
            ->rememberForever($key, function () use ($columns) {
                return parent::get($columns);
            });
    }

    public function max($column)
    {
        $tags = [str_slug(get_class($this->model))];
        $key = str_slug(get_class($this->model)) ."-max_{$column}";

        return $this->cache($tags)
            ->rememberForever($key, function () use ($column) {
                return parent::max($column);
            });
    }

    public function min($column)
    {
        $tags = [str_slug(get_class($this->model))];
        $key = str_slug(get_class($this->model)) ."-min_{$column}";

        return $this->cache($tags)
            ->rememberForever($key, function () use ($column) {
                return parent::min($column);
            });
    }

    public function pluck($column, $key = null)
    {
        $tags = $this->getCacheTags();
        $cacheKey = $this->getCacheKey([$column]) . "-pluck_{$column}";

        if ($key) {
            $cacheKey .= "_{$key}";
        }

        return $this->cache($tags)
            ->rememberForever($cacheKey, function () use ($column, $key) {
                return parent::pluck($column, $key);
            });
    }

    public function sum($column)
    {
        $tags = [str_slug(get_class($this->model))];
        $key = str_slug(get_class($this->model)) ."-sum_{$column}";

        return $this->cache($tags)
            ->rememberForever($key, function () use ($column) {
                return parent::sum($column);
            });
    }
}
