<?php
declare(strict_types=1);

namespace ResellNom\Registrar;

use InvalidArgumentException;

final class RegistrarManager
{
    /** @var array<string, RegistrarInterface> */
    private array $adapters = [];

    public function registerAdapter(string $name, RegistrarInterface $adapter): void
    {
        $this->adapters[strtolower($name)] = $adapter;
    }

    public function get(string $name): RegistrarInterface
    {
        $key = strtolower(trim($name));
        if (!isset($this->adapters[$key])) {
            throw new InvalidArgumentException('Registrar adapter is not configured: ' . $name);
        }
        return $this->adapters[$key];
    }
}
