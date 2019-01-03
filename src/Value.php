<?php

namespace Matthewnw\Metrics;

use Illuminate\Http\Request;

/**
 * Value class
 */
abstract class Value extends Metric {
    /**
     * Type of Metric
     *
     * @var string
     */
    protected static $type = 'value';

    /**
     * value for the metric at the previous range
     *
     * @var Array
     */
    protected $previous;

    /**
     * value for the metric at the previous range
     *
     * @var Array
     */
    protected $change;

    /**
     * change title for the metric
     *
     * @var Array
     */
    protected $changeLabel;

    /**
     * Manually set the result value
     *
     * @param mixed $value
     * @return Metric
     */
    protected function result($value)
    {
        $this->setValue($value);

        return $this;
    }

    /**
     * Manually set the previous value
     *
     * @param mixed $value
     * @return Metric
     */
    protected function previous($value)
    {
        $this->previous = $value;

        $this->calculateChange();

        return $this;
    }

    /**
     * caluclate the percentage change between the values
     *
     * @return void
     */
    protected function calculateChange()
    {
        $difference = (int) $this->value['value'] - (int) $this->previous;
        $this->change = round(($difference / (int) $this->value['value']) * 100, 2);

        if($this->previous && (int) $this->previous > 0){
            $this->changeLabel = abs($this->change) . '% ' . ($this->change > 0? 'Increase' : 'Decrease');
        }else{
            $this->changeLabel = 'No Prior Data';
        }
    }

    /**
     * Counts the values for model at the range and previous range
     *
     * @param Illuminate\Http\Request $request
     * @param Illuminate\Database\Eloquent\Model $model Eloquent model
     * @return Matthewnw\Metrics\Classes\Metric
     */
    protected function count(Request $request, $model, $dateColumn = 'created_at')
    {
        $this->setValue($model::where($dateColumn, '>=', $this->currentRangeDate)->count());
        $this->previous = $model::where($dateColumn, '>=', $this->previousRangeDate)
            ->where($dateColumn, '<', $this->currentRangeDate)
            ->count();

        $this->calculateChange();

        return $this;
    }

    /**
     * Sums the column for model at the range and previous range
     *
     * @param Illuminate\Http\Request $request
     * @param Illuminate\Database\Eloquent\Model $model eloquent model
     * @param String $column column in the database to sum for each row
     * @return Matthewnw\Metrics\Classes\Metric
     */
    protected function sum(Request $request, $model, $column, $dateColumn = 'created_at')
    {
        $this->setValue($model::where($dateColumn, '>=', $this->currentRangeDate)->sum($column));
        $this->previous = $model::where($dateColumn, '>=', $this->previousRangeDate)
            ->where($dateColumn, '<', $this->currentRangeDate)
            ->sum($column);

        $this->calculateChange();

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
            'previous' => $this->previous,
            'change' => $this->change,
            'changeLabel' => $this->changeLabel,
            'range' => $this->range,
            'ranges' => $this->ranges(),
        ];
    }
}
