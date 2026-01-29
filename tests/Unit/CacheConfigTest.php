<?php

declare(strict_types=1);

use Marko\Cache\Config\CacheConfig;
use Marko\Config\ConfigRepositoryInterface;
use Marko\Config\Exceptions\ConfigNotFoundException;

function createCacheConfigRepository(
    array $configData = [],
): ConfigRepositoryInterface {
    return new readonly class ($configData) implements ConfigRepositoryInterface
    {
        public function __construct(
            private array $data,
        ) {}

        public function get(
            string $key,
            ?string $scope = null,
        ): mixed {
            if (!$this->has($key, $scope)) {
                throw new ConfigNotFoundException($key);
            }

            return $this->data[$key];
        }

        public function has(
            string $key,
            ?string $scope = null,
        ): bool {
            return isset($this->data[$key]);
        }

        public function getString(
            string $key,
            ?string $scope = null,
        ): string {
            return (string) $this->get($key, $scope);
        }

        public function getInt(
            string $key,
            ?string $scope = null,
        ): int {
            return (int) $this->get($key, $scope);
        }

        public function getBool(
            string $key,
            ?string $scope = null,
        ): bool {
            return (bool) $this->get($key, $scope);
        }

        public function getFloat(
            string $key,
            ?string $scope = null,
        ): float {
            return (float) $this->get($key, $scope);
        }

        public function getArray(
            string $key,
            ?string $scope = null,
        ): array {
            return (array) $this->get($key, $scope);
        }

        public function all(
            ?string $scope = null,
        ): array {
            return $this->data;
        }

        public function withScope(
            string $scope,
        ): ConfigRepositoryInterface {
            return $this;
        }
    };
}

it('reads driver from config without fallback', function () {
    $config = new CacheConfig(createCacheConfigRepository([
        'cache.driver' => 'redis',
    ]));

    expect($config->driver())->toBe('redis');
});

it('reads path from config without fallback', function () {
    $config = new CacheConfig(createCacheConfigRepository([
        'cache.path' => '/var/cache',
    ]));

    expect($config->path())->toBe('/var/cache');
});

it('reads default_ttl from config without fallback', function () {
    $config = new CacheConfig(createCacheConfigRepository([
        'cache.default_ttl' => 7200,
    ]));

    expect($config->defaultTtl())->toBe(7200);
});

it('throws ConfigNotFoundException when driver is missing', function () {
    $config = new CacheConfig(createCacheConfigRepository([]));

    $config->driver();
})->throws(ConfigNotFoundException::class);

it('throws ConfigNotFoundException when path is missing', function () {
    $config = new CacheConfig(createCacheConfigRepository([]));

    $config->path();
})->throws(ConfigNotFoundException::class);

it('throws ConfigNotFoundException when default_ttl is missing', function () {
    $config = new CacheConfig(createCacheConfigRepository([]));

    $config->defaultTtl();
})->throws(ConfigNotFoundException::class);

it('config file contains all required keys with defaults', function () {
    $configFile = require dirname(__DIR__, 2) . '/config/cache.php';

    expect($configFile)->toHaveKey('driver');
    expect($configFile)->toHaveKey('path');
    expect($configFile)->toHaveKey('default_ttl');
});
