<?php

namespace App\Modules\Core\God;

use App\Modules\Core\God\Exceptions\NoProviderException;
use App\Modules\Core\God\Providers\ProviderInterface;
use Illuminate\Support\Str;

/**
 *
 */
class Generator
{
    public const GOD_DIR = 'God';

    private array $modules = [];

    private array $loadedProviders = [];

    private const EXECUTE_METHOD = 'factory';

    public function __construct()
    {
        $this->loadModules();
    }

    private function loadModules(): void
    {
        $modulesFile = base_path() . '/modules_statuses.json';
        $modules = json_decode(file_get_contents($modulesFile), true);
        $namespace = config('modules.namespace');

        foreach ($modules as $module => $status) {
            if (!$status) {
                continue;
            }

            $this->addModulePath(
                $module,
                $namespace . '\\' . $module . '\\' . self::GOD_DIR . '\\Providers'
            );
        }
    }

    public function addModulePath(string $module, string $path): void
    {
        $this->modules[$module] = $path;
    }

    public function getModulePath(string $module): string
    {
        return $this->modules[$module];
    }

    public function getModules(): array
    {
        return $this->modules;
    }

    /**
     * @param string $method
     * @return ProviderInterface
     * @throws NoProviderException
     */
    private function loadProvider(string $method): ProviderInterface
    {
        if (isset($this->loadedProviders[$method])) {
            return $this->loadedProviders[$method];
        }

        foreach ($this->modules as $module) {
            $classNamespace = $module . '\\' . ucfirst(Str::camel($method));
            if (!class_exists($classNamespace)) {
                continue;
            }

            if (!is_subclass_of($classNamespace, ProviderInterface::class)) {
                continue;
            }

            $this->loadedProviders[$method] = app($classNamespace);

            return $this->loadedProviders[$method];
        }

        throw new NoProviderException('No provider found for ' . $method);
    }

    /**
     * @throws NoProviderException
     */
    public function __call(string $method, array $args): mixed
    {
        return call_user_func_array(
            [$this->loadProvider($method), self::EXECUTE_METHOD],
            $args
        );
    }

    /**
     * @throws NoProviderException
     */
    public function mockClient(string $provider, array $methods = []): void
    {
        if (!isset($this->modules[ucfirst($provider)])) {
            throw new NoProviderException('No provider found for ' . $provider);

        }

        $classNamespace = $this->modules[ucfirst($provider)]
            . '\\ClientProvider';

        if (!is_subclass_of($classNamespace, ProviderInterface::class)) {
            throw new \Exception('ClientProvider must implement ProviderInterface');
        }
        $class = app($classNamespace);
        call_user_func_array([$class, self::EXECUTE_METHOD], $methods);
    }
}
