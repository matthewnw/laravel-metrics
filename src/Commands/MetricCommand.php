<?php

namespace Matthewnw\Metrics\Commands;

use Illuminate\Console\Command;

trait MetricCommand
{
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = studly_case($this->argument('name'));
        // Create a metric class file
        $this->metric($name);
        // Output
        $this->line("$this->type metric created.");
    }

    /**
     * Create the metric class file
     *
     * @param string $name
     * @return void
     */
    protected function metric($name)
    {
        $metricTemplate = str_replace(
            ['{{metricName}}'],
            [$name],
            $this->getStub()
        );

        file_put_contents(app_path("/Metrics/{$name}.php"), $metricTemplate);
    }

    /**
     * Get contents of the stub file
     *
     * @return void
     */
    protected function getStub()
    {
        return file_get_contents("./stubs/$this->type.php.stub");
    }
}
