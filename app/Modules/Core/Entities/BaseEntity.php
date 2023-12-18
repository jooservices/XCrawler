<?php

namespace App\Modules\Core\Entities;

use App\Modules\Core\Entities\Traits\HasCasting;
use App\Modules\Core\Exceptions\EntityCastException;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BaseEntity implements EntityInterface
{
    use HasCasting;

    protected array $data = [];
    protected array $fields = [];
    protected array $initialData = [];

    public function __construct($data = [])
    {
        if (is_object($data)) {
            $data = (array)$data;
        }

        $this->setData($data);
    }

    public function setData(array $data): void
    {
        if (empty($this->fields)) {
            return;
        }

        foreach ($data as $item => $value) {
            $this->set($item, $value);
        }
    }

    /**
     * @param string $name
     * @param $default
     * @return mixed|null
     * @throws EntityCastException
     */
    public function get(string $name, $default = null): mixed
    {
        if (!isset($this->data[$name])) {
            return $default;
        }

        // Allow overriding of getters
        $method = 'get' . ucfirst(Str::camel($name));
        if (method_exists($this, $method)) {
            return $this->{$method}($name, $default);
        }

        if (!isset($this->casts[$name])) {
            return $this->data[$name];
        }

        // Use casts if available
        $method = 'castingTo' . ucfirst($this->casts[$name]);
        if (method_exists($this, $method)) {
            return $this->{$method}($this->data[$name]);
        }

        return $default;
    }

    public function set(string $name, $value): void
    {
        $this->data[$name] = $value;
        $this->initialData[$name] = true;
    }

    /**
     * @throws Exception
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    public function __set(string $name, $value)
    {
        $this->set($name, $value);
    }

    public function __isset(string $name)
    {
        return isset($this->data[$name]);
    }

    public function __unset(string $name)
    {
        unset($this->data[$name]);
        unset($this->initialData[$name]);
    }

    public function toArray(): array
    {
        if (empty($this->fields)) {
            return $this->data;
        }

        $data = [];

        foreach ($this->fields as $field) {
            if (isset($this->data[$field])) {
                $data[$field] = $this->data[$field];
                continue;
            }

            $data[$field] = null;
        }

        return $data;
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    public function toObject(): Collection
    {
        return collect($this->toArray());
    }
}
