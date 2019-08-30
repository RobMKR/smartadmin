<?php

/**
 * @see https://github.com/GeneaLabs/laravel-model-caching
 */

namespace MgpLabs\SmartAdmin\Modules\Cache;

use MgpLabs\SmartAdmin\Modules\Cache\CachedBuilder as Builder;
use Illuminate\Cache\TaggableStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

abstract class CachedModel extends Model
{
    public function newEloquentBuilder($query)
    {
        return $this->isCached() ? new Builder($query) : new EloquentBuilder($query);
    }

    public static function boot()
    {
        parent::boot();
        $class = get_called_class();
        $instance = new $class;

        static::created(function () use ($instance) {
            $instance->flushCache();
        });

        static::deleted(function () use ($instance) {
            $instance->flushCache();
        });

        static::saved(function () use ($instance) {
            $instance->flushCache();
        });

        static::updated(function () use ($instance) {
            $instance->flushCache();
        });
    }

    public function cache(array $tags = [])
    {
        $cache = cache();

        if (is_subclass_of($cache->getStore(), TaggableStore::class)) {
            array_push($tags, str_slug(get_called_class()));
            $cache = $cache->tags($tags);
        }

        return $cache;
    }

    public function flushCache(array $tags = [])
    {
        $this->cache($tags)->flush();
    }

    public static function all($columns = ['*'])
    {
        $class = get_called_class();
        $instance = new $class;

        $tags = [str_slug(get_called_class())];
        $key = $instance->getTable() . '_' . implode('-', $columns) . '_' . implode('-', $tags) . '_all';

        return $instance->cache($tags)
            ->rememberForever($key, function () use ($columns) {
                return parent::all($columns);
            });
    }
}
