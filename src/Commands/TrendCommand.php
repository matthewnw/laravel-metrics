<?php

namespace Matthewnw\Metrics\Commands;

use Illuminate\Console\Command;

class TrendCommand extends Command
{
    use MetricCommand;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metric:trend {name : The name of the metric class}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new trend metric class';

    /**
     * Type of metric
     *
     * @var string
     */
    protected $type = 'Trend';
}
