<?php

declare(strict_types=1);

namespace App\Config;

class Configuration
{
    private array $configuration;

    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    public function get(string $key, $default = null)
    {
        $segments = explode('.', $key);
        $data = $this->configuration;

        foreach ($segments as $segment) {
            $data = isset($data[$segment]) ? $data[$segment] : $default;
        }

        return $data;
    }

    public function set(string $key, $value): void
    {
        $this->configuration[$key] = $value;
    }

    public function all(): array
    {
        return $this->configuration;
    }

    public function has(string $key): bool
    {
        return isset($this->configuration[$key]);
    }

    public function remove(string $key): void
    {
        unset($this->configuration[$key]);
    }

    public function clear(): void
    {
        $this->configuration = [];
    }
}
