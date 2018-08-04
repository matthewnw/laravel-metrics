<?php

namespace Matthewnw\Metrics;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Partition class
 */
abstract class Partition extends Metric {
    /**
     * Type of Metric
     *
     * @var string
     */
    protected static $type = 'partition';

    /**
     * collection of values for the metric based on the model
     *
     * @var Array
     */
    protected $values; // ['text', 'value']

    /**
     * Generate a cache key for the relevant metric type and request
     *
     * @return string
     */
    protected function getCacheKey()
    {
        return get_class($this).':'.static::$type;
    }

    /**
     * Counts the values for model at the range and previous range
     *
     * @param Illuminate\Http\Request $request
     * @param Illuminate\Database\Eloquent\Model $model Eloquent model
     * @return Matthewnw\Metrics\Classes\Metric
     */
    protected function count(Request $request, $model, $groupColumn, $dateColumn = 'created_at')
    {
        $this->values = DB::table(with(new $model)->getTable())
            ->select("$groupColumn as text", DB::raw('count(*) as value'))
            ->groupBy($groupColumn)->orderBy($groupColumn, 'asc')->get();

        return $this;
    }

    /**
     * Convert the Metric instance to an array.
     *
     * @return Array
     */
    public function toArray()
    {
        return $this->cache?? [
            'type' => self::$type,
            'title' => $this->title,
            'icon' => $this->icon,
            'values' => $this->values,
        ];
    }
}
