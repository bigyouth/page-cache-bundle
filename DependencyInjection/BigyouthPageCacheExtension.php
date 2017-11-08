<?php

namespace Bigyouth\BigyouthPageCacheBundle\DependencyInjection;

use Bigyouth\BigyouthPageCacheBundle\Listener\PageCacheListener;
use Bigyouth\BigyouthPageCacheBundle\Services\PageCacheService;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class BigyouthPageCacheExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config        = $this->processConfiguration($configuration, $configs);
        $this->buildComponents($config, $container);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }

    private function buildComponents($config, ContainerBuilder $container)
    {
        $cacheService = $this->buildService($config, $container);
        //$this->buildListener($cacheService, $container);
    }

    /**
     * @param $config
     * @param ContainerBuilder $container
     * @return Definition
     */
    private function buildService($config, ContainerBuilder $container)
    {
        $default = $config['default'];

        $pageCacheService          = new Definition(PageCacheService::class);
        $pageCacheServiceArguments = array(
            'enabled' => $default['enabled'],
            'ttl'     => $default['ttl'],
            'exclude' => $default['exclude'],
            'type'    => $default['type']
        );
        $pageCacheService->setArguments($pageCacheServiceArguments);
        $managerId = 'by.page_cache';
        $pageCacheService->setPublic(true);

        $service = $container->setDefinition($managerId, $pageCacheService);

        return $service;
    }

    /**
     * @param $cacheService
     * @param ContainerBuilder $container
     */
    private function buildListener($cacheService, ContainerBuilder $container)
    {
        $pageCacheListener = new Definition(PageCacheListener::class);

        $managerId = 'by.page_cache.listener';
        $pageCacheListener->setPublic(true);
        $pageCacheListener->addTag('kernel.event_subscriber');
        $pageCacheListener->setArguments([
            'tokenStorage' => TokenStorage::class,
            'cacheService' => $cacheService
        ]);

        $container->setDefinition($managerId, $pageCacheListener);
    }
}
