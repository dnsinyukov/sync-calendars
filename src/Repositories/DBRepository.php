<?php

namespace Dnsinyukov\SyncCalendars\Repositories;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class DBRepository
 * @package Dnsinyukov\SyncCalendars\Repositories
 *
 * @method delete($id = null)
 * @method where($column, $operator = null, $value = null, $boolean = 'and')
 * @method whereIn($column, $values, $boolean = 'and', $not = false)
 * @method whereNotIn($column, $values, $boolean = 'and')
 * @method get($columns = ['*'])
 * @method updateOrInsert($attributes, array $values = [])
 * @method insert(array|array[] $values)
 */
abstract class DBRepository
{
    /**
     * @var string[]
     */
    protected $columns = ['*'];

    /**
     * @return mixed
     */
    abstract public function getTable();

    /**
     * Create a new record
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function create(array $attributes)
    {
        return $this->query()->insertGetId($attributes);
    }

    /**
     * Get query
     *
     * @return Builder
     */
    public function query()
    {
        return DB::table($this->getTable());
    }

    /**
     * Get resources by an array of attributes
     */
    public function getByAttributes(array $attributes, $orderBy = null, $sortOrder = 'asc'): Collection
    {
        $query = $this->buildQueryByAttributes($attributes, $orderBy, $sortOrder);

        return $query->get($this->columns);
    }

    /**
     * Find resource by an array of attributes
     */
    public function findByAttributes(array $attributes, $orderBy = null, $sortOrder = 'asc')
    {
        $query = $this->buildQueryByAttributes($attributes, $orderBy, $sortOrder);

        return $query->first($this->columns);
    }

    /**
     * Build Query to catch resources by an array of attributes and params
     *
     * @param array $attributes
     * @param string|null $orderBy
     * @param string $sortOrder
     * @return Builder
     */
    protected function buildQueryByAttributes(array $attributes, string $orderBy = null, string $sortOrder = 'asc'): Builder
    {
        $query = $this->query();

        foreach ($attributes as $field => $value) {
            if (is_array($value)) {
                $query = $query->whereIn($field, $value);
            } else {
                $query = $query->where($field, $value);
            }
        }

        if (null !== $orderBy) {
            $query = $query->orderBy($orderBy, $sortOrder);
        }

        return $query;
    }

    /**
     * @param array $attributes
     *
     * @return int
     */
    public function deleteWhere(array $attributes): int
    {
        return $this->buildQueryByAttributes($attributes)->delete();
    }

    /**
     * @param string $columns
     * @param array $attributes
     *
     * @return int
     */
    public function count(array $attributes = [], string $columns = '*'): int
    {
        $query = $this->buildQueryByAttributes($attributes);

        return $query->count($columns);
    }

    /**
     * Update by attributes
     *
     * @param array $attributes
     * @param array $data
     * @return bool
     */
    public function updateByAttributes(array $attributes, array $data) : bool
    {
        return $this->buildQueryByAttributes($attributes)->update($data);
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return false|mixed
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->query(), $name)) {
            return call_user_func([$this->query(), $name], ...$arguments);
        }

        throw new \RuntimeException('Method not found', 500);
    }

    /**
     * @param array $columns
     * @return $this
     */
    public function setColumns(array $columns): self
    {
        $this->columns = $columns;

        return $this;
    }
}
