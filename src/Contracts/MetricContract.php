<?php

namespace Matthewnw\Metrics\Contracts;

use Illuminate\Http\Request;

Interface MetricContract
{
    public function calculate(Request $request);

    public function cacheFor();
}
