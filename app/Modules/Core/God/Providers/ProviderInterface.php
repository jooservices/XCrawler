<?php

namespace App\Modules\Core\God\Providers;

interface ProviderInterface
{
    public function factory(...$args): self;
}
