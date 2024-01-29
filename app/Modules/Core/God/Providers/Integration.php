<?php

namespace App\Modules\Core\God\Providers;

use App\Modules\Client\Models\Integration as Model;

class Integration implements ProviderInterface
{
    private Model $model;

    public function factory(...$args): self
    {
        $this->model = Model::factory()
            ->create();

        return $this;
    }

    public function isPrimary(bool $isPrimary = true): self
    {
        $this->model->update([
            'is_primary' => $isPrimary
        ]);

        return $this;
    }

    public function service(string $service): self
    {
        $this->model->update([
            'service' => $service
        ]);

        return $this;
    }

    public function get(): Model
    {
        return $this->model;
    }
}
