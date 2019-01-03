<?php

namespace Matthewnw\Metrics;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Trend class
 */
abstract class Trend extends Metric {
    /**
     * Type of Metric
     *
     * @var string
     */
    protected static $type = 'trend';

    /**
     * collection of values for the metric based on the model
     *
     * @var Array
     */
    protected $values; // [['date', 'text', 'value'], ...]

    /**
     * whether to add a showValue to the response
     *
     * @var Array
     */
    protected $showLatestValue = false;

    /**
     * Add a value on the response to display the current value
     *
     * @return void
     */
    protected function showLatestValue()
    {
        $this->showLatestValue = true;
        return $this;
    }

    /**
     * Manually set the results for the metric
     *
     * @param array $values [['date', 'text', 'value'], ...]
     * @return Metric
     */
    protected function result($values)
    {
        $this->values = $values;

        return $this;
    }

    protected function countByDays(Request $request, $model, $dateColumn = 'created_at')
    {
        // Get total models grouped by created_at day
        $query = DB::table(with(new $model)->getTable())
            ->select(DB::raw('count(*) as value, count(*) as text'), DB::raw("DATE($dateColumn) as date"))
            ->where($dateColumn, '>=', $this->currentRangeDate)
            ->orderBy('date', 'asc')->groupBy('date')->get();

        $dailyCount = [];
        $days = $this->currentRangeDate->diffInDays(Carbon::now());
        $date = clone $this->currentRangeDate;
        // Do a loop for each day whether there is a resource or not
        for($i=0; $i <= $days; $i++){
            $date->startOfDay();
            $found = $query->firstWhere('date', $date->toDateString());

            $dailyCount[] = [
                'date' => $date->toDateString(),
                'value' => $found? $found->value : 0,
                'text' => $found? $found->value : 0,
            ];
            $date->addDay();
        }

        $this->values = collect($dailyCount);

        $latest = end($dailyCount); reset($dailyCount); // reset internal pointer
        $this->setValue($latest['value']);

        return $this;
    }

    protected function countByWeeks(Request $request, $model, $dateColumn = 'created_at')
    {
        return $this;
    }

    protected function sumByDays(Request $request, $model, $column, $dateColumn = 'created_at')
    {
        return $this;
    }

    protected function sumByWeeks(Request $request, $model, $column, $dateColumn = 'created_at')
    {
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
            'value' => $this->value,
            'values' => $this->values,
            'range' => $this->range,
            'ranges' => $this->ranges(),
            'showLatest' => $this->showLatestValue,
        ];
    }
}
