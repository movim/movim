<?php

namespace Movim;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel
{
    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'composite';

    /**
     * Get the value indicating whether the IDs are incrementing.
     *
     * @return bool
     */
    public function getIncrementing()
    {
        return false;
    }

    /**
     * Set the keys for a save update query.
     *
     * @param  Builder $query
     *
     * @return Builder
     * @throws Exception
     */
    protected function setKeysForSaveQuery($query)
    {
        foreach ($this->getKeyName() as $key) {
            if (! isset($this->$key)) {
                throw new Exception(__METHOD__ . 'Missing part of the primary key: ' . $key);
            }

            $query->where($key, '=', $this->$key);
        }

        return $query;
    }

    /**
     * Catch the save Exceptions to log them properly
     *
     * @param array $options
     */
    public function save(array $options = [])
    {
        try {
            return parent::save($options);
        } catch (\Exception $e) {
            (new Bootstrap)->exceptionHandler($e);
        }
    }

    /**
     * Execute a query for a single record by ID.
     *
     * @param  array $ids Array of keys, like [column => value].
     * @param  array $columns
     *
     * @return mixed|static
     */
    public static function find($ids, $columns = ['*'])
    {
        $me    = new self;
        $query = $me->newQuery();
        foreach ($me->getKeyName() as $key) {
            $query->where($key, '=', $ids[$key]);
        }

        return $query->first($columns);
    }

    /**
     * Reload a fresh model instance from the database.
     *
     * @param  array|string  $with
     * @return static|null
     */
    public function fresh($with = [])
    {
        if (! $this->exists) {
            return;
        }

        $primaryKey = $this->getKeyName();
        $query = static::newQueryWithoutScopes()
            ->with(is_string($with) ? func_get_args() : $with);

        foreach (is_array($primaryKey) ? $primaryKey : [] as $key) {
            $query->where($key, '=', $this->$key);
        }

        return $query->first();
    }
}
