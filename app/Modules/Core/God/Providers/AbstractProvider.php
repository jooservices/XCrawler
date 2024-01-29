<?php

namespace App\Modules\Core\God\Providers;

use ReflectionClass;

abstract class AbstractProvider implements ProviderInterface
{
    protected function getFixture(string $fileName): false|string
    {
        $dir = dirname((new ReflectionClass($this))->getFileName());


        return file_get_contents($dir . '/../Fixtures/' . $fileName);
    }
}
