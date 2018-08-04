<?php

namespace Matthewnw\Metrics\Commands;

use Illuminate\Console\Command;

class ValueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metric:value';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new value metric class';

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
        $this->line('Value metric created.');
    }
}
