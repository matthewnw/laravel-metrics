<?php

namespace Matthewnw\Metrics;

use Matthewnw\Metrics\Contracts\MetricContract;
use JsonSerializable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Container\Container;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Cache;

/**
 * Metric class.
 */
abstract class Metric implements MetricContract, Arrayable, Jsonable, JsonSerializable
{
    /**
     * latest value for the metric based on the model
     *
     * @var Array
     */
    protected $value;

    /**
     * Title for the metric
     *
     * @var String
     */
    protected $title;

    /**
     * Icon for the metric
     *
     * @var String
     */
    protected $icon;

    /**
     * Range for the metric
     *
     * @var String
     */
    protected $range = null;

    /**
     * Http Request instance
     *
     * @var Request
     */
    protected $request;

    /**
     * latest cached value for the metric
     *
     * @var Array
     */
    protected $cache = null;

    /**
     * latest cached value for the metric
     *
     * @var Array
     */
    protected $currentRangeDate;

    /**
     * latest cached value for the metric
     *
     * @var Array
     */
    protected $previousRangeDate;

    /**
     * Constructor for a metric
     */
    public function __construct()
    {
        $this->request = Container::getInstance()->make('request');
        $this->title = $this->getTitleFromClassName();

        // Set the current and previous ranges
        $this->currentRangeDate = $this->getRange($this->request);
        $this->previousRangeDate = $this->getRange($this->request, $this->currentRangeDate);

        // Check the cache and set if not found
        if($cacheFor = $this->cacheFor()){
            $this->cache = Cache::remember($this->getCacheKey(), $cacheFor, function() use($cacheFor) {
                $this->calculate($this->request);
                // Set this toArray as the cache with the expiry appended to the response data
                return $this->toArray() + ['lastUpdated' => Carbon::now()->toDateTimeString(), 'expires' => $cacheFor->toDateTimeString()];
            });
        }else{
            // reset the cache key if not caching
            Cache::forget($this->getCacheKey());
            // Run the calculations for the metric
            $this->calculate($this->request);
        }
    }

    /**
     * Generate a cache key for the relevant metric type and request
     *
     * @return string
     */
    protected function getCacheKey()
    {
        return get_class($this).':'.static::$type.':range:'. $this->currentRangeDate->toDateString();
    }

    /**
     * Return the key for the default range if none provided
     * defaults to the first in the range list
     *
     * @return int|string
     */
    public function defaultRange()
    {
        return key($this->ranges());
    }

    /**
     * Get the range in Carbon format from the request
     *
     * @param Illuminate\Http\Request $request
     * @return string Date string formatteed from Carbon\Carbon
     */
    protected function getRange(Request $request, $date = null)
    {
        if (! $date) {
            $date = Carbon::now();
        }else{
            $date = (clone $date);
            if($this->range == 'MTD') $date->subMonth(); // go to previous month if date is set only for MTD
        }

        if($request->range && $request->range != 'null'){
            $this->range = $request->range;
        }else{
            $this->range = $this->defaultRange();
        }

        if(is_numeric($this->range)){
            return $date->subDays((int)$this->range);
        }else{
            switch($this->range){
                case 'MTD':
                    return $date->startOfMonth();
                    break;
                case 'QTD':
                    return $date->subMonths(3);
                    break;
                case 'YTD':
                    return $date->subYear();
                    break;
            }
        }
        throw new \Exception('Invalid range provided');
    }

    /**
     * Set the value array
     *
     * @param Integer $value
     * @param String $label
     * @return mixed
     */
    public function setValue($value, $label = null)
    {
        $this->value = [
            'text' => $label?? $value,
            'value' => $value,
        ];
        return $this->value;
    }

    /**
     * Prefix all value text with provided string or character
     *
     * @param string $character
     * @param string $type prefix | suffix
     * @return Matthewnw\Metrics\Classes\Metric
     */
    protected function prefixSuffix($character, $type)
    {
        if(property_exists($this, 'values')){
            $this->values->transform(function($item) use ($character, $type){
                if(is_array($item)){
                    $item['text'] = ($type == 'prefix'? $character : '') . $item['text'] . ($type == 'suffix'? $character : '');
                }else{
                    $item->text = ($type == 'prefix'? $character : '') . $item->text . ($type == 'suffix'? $character : '');
                }
                return $item;
            });
        }
        if(is_array($this->value)){
            if($type == 'prefix'){
                $this->value['text'] = $character . $this->value['text'];
            }else if($type == 'suffix'){
                $this->value['text'] .= $character;
            }
        }
        return $this;
    }

    /**
     * Prefix all value text with provided string or character
     *
     * @return void
     */
    protected function prefix($character)
    {
        return $this->prefixSuffix($character, 'prefix');
    }

    /**
     * Suffix all value text with provided string or character
     *
     * @return void
     */
    protected function suffix($character)
    {
        return $this->prefixSuffix($character, 'suffix');
    }

    /**
     * Prefix all values with a Pound sign
     *
     * @return void
     */
    protected function pounds()
    {
        return $this->prefix('£');
    }

    /**
     * Prefix all values with a Dollar sign
     *
     * @return void
     */
    protected function dollars()
    {
        return $this->prefix('$');
    }

    /**
     * Manually set the title from a string
     *
     * @return void
     */
    public function title($string)
    {
        $this->title = $string;

        return $this;
    }

    /**
     * Manually set the icon from a string
     *
     * @return void
     */
    public function icon($string)
    {
        $this->icon = $string;

        return $this;
    }

    /**
     * Set the label for each value(s) by passing a callback function with the current value
     *
     * @param closure $closure
     * @return Matthewnw\Metrics\Classes\Metric
     */
    public function label($closure)
    {
        if(property_exists($this, 'values')){
            $this->values->transform(function($item) use($closure){
                if(is_array($item)){
                    $item['text'] = $closure($item['text']);
                }else{
                    $item->text = $closure($item->text);
                }
                return $item;
            });
        }else if(is_array($this->value)){
            $this->value['text'] = $closure($this->value['text']);
        }

        return $this;
    }

    /**
     * Generate a sensible title for the metric based on the class name
     *
     * @return void
     */
    public function getTitleFromClassName()
    {
        $class = class_basename($this);
        // Separate string by capital letters and join with a space (removing whitespace from ends)
        $title = trim(implode(' ', preg_split('/(?=[A-Z])/', $class)));
        return title_case($title);
    }

    /**
     * Array of dropdown range selections for the metric
     * values can be either an integer or MTD, QTD, YTD
     *
     * @return Array
     */
    public function ranges()
    {
        return [
            30 => '30 Days',
            60 => '60 Days',
            'MTD' => 'Month to Date',
            'QTD' => 'Quarter to Date',
            'YTD' => 'Year to Date',
        ];
    }

    /**
     * Convert the Metric instance to an array.
     *
     * @return Array
     */
    public function toArray()
    {
        return $this->cache?? [
            'title' => $this->title,
            'icon' => $this->icon,
            'value' => $this->value,
            'range' => $this->range,
            'ranges' => $this->ranges(),
        ];
    }

    /**
     * Convert the Metric instance to JSON.
     *
     * @param  int  $options
     * @return string
     *
     * @throws \Illuminate\Database\Eloquent\JsonEncodingException
     */
    public function toJson($options = 0)
    {
        $json = json_encode($this->jsonSerialize(), $options);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \Exception(json_last_error_msg());
        }

        return $json;
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Determine for how many minutes the metric should be cached
     * return either a DateTime / Carbon or integer for minutes
     *
     * @return \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        // return now()->addMinutes(5);
    }
}
