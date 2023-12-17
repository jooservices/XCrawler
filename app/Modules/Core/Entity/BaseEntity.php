<?php

namespace App\Modules\Core\Entity;

use App\Modules\Core\Entity\Traits\HasCasting;
use App\Modules\Core\Exceptions\EntityCastException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BaseEntity implements EntityInterface
{
    use HasCasting;

    protected array $data = [];

    public function __construct($data = [])
    {
        if (is_object($data)) {
            $data = (array)$data;
        }

        $this->data = $data;
    }

    /**
     * @throws \Exception
     */
    public function get(string $name, $default = null)
    {
        $method = 'get' . ucfirst(Str::camel($name));

        if (method_exists($this, $method)) {
            return $this->$method();
        }

        if (!isset($this->casts[$name])) {
            return $this->data[$name] ?? $default;
        }

        $cast = $this->casts[$name];

        if (method_exists($this, 'to' . ucfirst($cast))) {
            return $this->$cast($this->data[$name]);
        }

        throw new EntityCastException("Cast method {$cast} does not exist");
    }

    public function set(string $name, $value): void
    {
        $this->data[$name] = $value;
    }

    /**
     * @throws \Exception
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    public function __set(string $name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __isset(string $name)
    {
        return isset($this->data[$name]);
    }

    public function __unset(string $name)
    {
        unset($this->data[$name]);
    }

    /**
     * @throws \Exception
     */
    public function __call(string $name, array $arguments)
    {
        $method = str_starts_with($name, 'get') ? 'get' : 'set';
        $property = substr($name, 3);

        if ($method === 'get') {
            return $this->get(Str::snake($property));
        }

        $this->set($property, $arguments[0]);
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function toJson(): string
    {
        return json_encode($this->data);
    }

    public function toObject(): Collection
    {
        return collect($this->data);
    }
}
