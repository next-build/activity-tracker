<?php

use Laravel\Octane\Exceptions\DdException;

if (! function_exists('dd')) {
    function dd(...$vars)
    {
        throw new DdException($vars);
    }
}
