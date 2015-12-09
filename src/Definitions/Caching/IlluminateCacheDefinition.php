<?php

/*
 * This file is part of madewithlove/definitions
 *
 * (c) madewithlove <heroes@madewithlove.be>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace Madewithlove\Definitions\Definitions\Caching;

use Assembly\ObjectDefinition;
use Assembly\Reference;
use Illuminate\Cache\FileStore;
use Illuminate\Cache\RedisStore;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Cache\Repository as RepositoryInterface;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Filesystem\Filesystem;
use Madewithlove\Definitions\Definitions\AbstractDefinitionProvider;
use Redis;

class IlluminateCacheDefinition extends AbstractDefinitionProvider
{
    /**
     * @var string
     */
    protected $driver;

    /**
     * @var array
     */
    protected $configuration;

    /**
     * @param string $driver
     * @param array  $configuration
     */
    public function __construct($driver = FileStore::class, array $configuration = [])
    {
        $this->driver = $driver;
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinitions()
    {
        return [
            Filesystem::class => new ObjectDefinition(Filesystem::class),
            Redis::class => new ObjectDefinition(Redis::class),
            Store::class => $this->getStore(),
            RepositoryInterface::class => $this->getRepository(),
        ];
    }

    /**
     * @return ObjectDefinition
     */
    protected function getStore()
    {
        $store = new ObjectDefinition($this->driver);

        switch ($this->driver) {
            case RedisStore::class:
                $store->setConstructorArguments(new Reference(Redis::class), ...$this->configuration);
                break;

            default:
            case FileStore::class:
                $store->setConstructorArguments(new Reference(Filesystem::class), ...$this->configuration);
                break;
        }

        return $store;
    }

    /**
     * @return ObjectDefinition
     */
    protected function getRepository()
    {
        $repository = new ObjectDefinition(Repository::class);
        $repository->setConstructorArguments(new Reference(Store::class));

        return $repository;
    }
}
