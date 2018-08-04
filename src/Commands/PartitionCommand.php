<?php

namespace Matthewnw\Metrics\Commands;

use Illuminate\Console\Command;

class PartitionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metric:partition';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new partition metric class';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->line('Partition metric created.');
    }
}
