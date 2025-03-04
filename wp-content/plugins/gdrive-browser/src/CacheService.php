<?php

namespace App;


use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Exception\CacheException;
use Symfony\Component\Cache\Exception\InvalidArgumentException;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * CacheService with pluggable adapters
 */
class CacheService
{

    private CacheItemPoolInterface $cache;

    /**
     * @var int Default TTL in seconds (30 days)
     */
    private int $defaultTtl = 2592000;

    /**
     * Constructor
     *
     * @param string $adapter Cache adapter type ('filesystem', 'array', 'redis', 'memcached')
     * @param array $options Adapter-specific options
     *
     * @throws CacheException
     */
    public function __construct(string $adapter = 'filesystem', array $options = [])
    {
        // Set default TTL from WordPress if available
        if (function_exists('get_option')) {
            $this->defaultTtl = (int)get_option('gdi_cache_duration', $this->defaultTtl);
        }

        // Initialize the appropriate adapter
        $this->initializeAdapter($adapter, $options);
    }

    /**
     * Initialize a cache adapter
     *
     * @param string $adapter Adapter type
     * @param array $options Adapter options
     *
     * @return void
     * @throws CacheException
     */
    private function initializeAdapter(string $adapter, array $options = []): void
    {
        switch ($adapter) {
            case 'array':
                $this->cache = new ArrayAdapter(
                    $options['defaultLifetime'] ?? $this->defaultTtl,
                    $options['storeSerialized'] ?? true
                );
                break;

            case 'redis':
                if (!isset($options['redis'])) {
                    throw new \InvalidArgumentException('Redis connection is required for Redis adapter');
                }

                $this->cache = new RedisAdapter(
                    $options['redis'],
                    $options['namespace'] ?? 'g-drive-cache',
                    $options['defaultLifetime'] ?? $this->defaultTtl
                );
                break;

            case 'memcached':
                if (!isset($options['memcached'])) {
                    throw new \InvalidArgumentException('Memcached connection is required for Memcached adapter');
                }

                $this->cache = new MemcachedAdapter(
                    $options['memcached'],
                    $options['namespace'] ?? 'g-drive-cache',
                    $options['defaultLifetime'] ?? $this->defaultTtl
                );
                break;

            case 'filesystem':
            default:
                // Get cache directory path, fallback to plugin's cache dir if WordPress functions not available
                $cacheDir = defined('GDI_CACHE_DIR') ? GDI_CACHE_DIR : __DIR__ . '/../cache';

                $this->cache = new FilesystemAdapter(
                    $options['namespace'] ?? 'g-drive-cache',
                    $options['defaultLifetime'] ?? $this->defaultTtl,
                    $options['directory'] ?? $cacheDir
                );
                break;
        }
    }

    /**
     * Store data in the cache
     *
     * @param string $key Cache key
     * @param mixed $data Data to store
     * @param int|null $ttl Time to live in seconds (null for default)
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function store(string $key, mixed $data, ?int $ttl = null): void
    {
        $ttl = $ttl ?? $this->defaultTtl;

        $this->cache->get(
            $key, function (ItemInterface $item) use ($data, $ttl) {
            $item->expiresAfter($ttl);

            return $data;
        }
        );
    }

    /**
     * Get data from the cache
     *
     * @param string $key Cache key
     *
     * @return mixed Data or null if not found
     * @throws InvalidArgumentException
     */
    public function get(string $key): mixed
    {
        $item = $this->cache->getItem($key);

        return $item->isHit() ? $item->get() : null;
    }

    /**
     * Clear an item from the cache
     *
     * @param string $key Cache key
     *
     * @return bool True if successful
     * @throws InvalidArgumentException
     */
    public function clear(string $key): bool
    {
        return $this->cache->deleteItem($key);
    }

    /**
     * Clear all items from the cache
     *
     * @return bool True if successful
     */
    public function clearAll(): bool
    {
        return $this->cache->clear();
    }

    /**
     * Get the current cache adapter
     *
     * @return CacheItemPoolInterface
     */
    public function getAdapter(): CacheItemPoolInterface
    {
        return $this->cache;
    }

    /**
     * Set a new cache adapter
     *
     * @param CacheItemPoolInterface $adapter
     *
     * @return void
     */
    public function setAdapter(CacheItemPoolInterface $adapter): void
    {
        $this->cache = $adapter;
    }
}
