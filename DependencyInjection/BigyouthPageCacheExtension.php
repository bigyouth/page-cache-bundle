<?php
namespace Bigyouth\BigyouthPageCacheBundle\DependencyInjection;

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
        $this->buildService($config, $container);
    }

    /**
     * @param $config
     * @param ContainerBuilder $container
     * @return Definition
     */
    private function buildService($config, ContainerBuilder $container)
    {
        $pageCacheService          = new Definition(PageCacheService::class);
        $pageCacheServiceArguments = array(
            $config['enabled'],
            $config['ttl'],
            $config['exclude'],
            $config['type'],
            $config['redis_host'],
            $config['redis_port'],
            $config['type'],
            $container->getParameter('kernel.cache_dir') . '/../by_cache'
        );
        $pageCacheService->setArguments($pageCacheServiceArguments);
        $managerId = 'by.page_cache';
        $pageCacheService->setPublic(true);

        $service = $container->setDefinition($managerId, $pageCacheService);

        return $service;
    }
}
