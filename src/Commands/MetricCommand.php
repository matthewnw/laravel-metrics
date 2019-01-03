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
        $name = $this->argument('name');
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
        $class = class_basename($name);
        $directory = app_path('Metrics/' . substr($name, 0,strrpos($name, '/')));
        $namespace = str_replace('/', '\\', $name);

        $metricTemplate = str_replace(
            ['{{namespace}}','{{className}}'],
            [$namespace, $class],
            $this->getStub()
        );

        if (! is_dir($directory)) {
            // dir doesn't exist, make it recursively
            mkdir($directory, null, true);
        }

        file_put_contents("{$directory}/{$class}.php", $metricTemplate);
    }

    /**
     * Get contents of the stub file
     *
     * @return void
     */
    protected function getStub()
    {
        return file_get_contents(__DIR__ . "/../../templates/$this->type.stub");
    }
}
