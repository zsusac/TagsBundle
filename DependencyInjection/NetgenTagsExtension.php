<?php

namespace Netgen\TagsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Yaml\Yaml;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ConfigurationProcessor;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;

/**
 * This is the class that loads and manages the bundle configuration.
 */
class NetgenTagsExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('services.yml');
        $loader->load('fieldtypes.yml');
        $loader->load('persistence.yml');
        $loader->load('papi.yml');
        $loader->load('default_settings.yml');
        $loader->load('templating.yml');

        if ($container->hasParameter('ezpublish.view_provider.registry.class')) {
            $loader->load('view.yml');
        }

        if ($container->hasParameter('ezpublish.persistence.legacy.search.gateway.sort_clause_handler.common.field.class')) {
            $loader->load('storage_engines/legacy/search_query_handlers.yml');
        } else {
            $loader->load('storage_engines/legacy/search_query_handlers_new_namespaces.yml');
        }

        if (interface_exists('eZ\Publish\API\Repository\Values\User\UserReference')) {
            $loader->load('limitations_user_reference.yml');
        } else {
            $loader->load('limitations_user.yml');
        }

        $loader->load('storage_engines/legacy.yml');

        $loader->load('storage_engines/solr/criterion_visitors.yml');

        $processor = new ConfigurationProcessor($container, 'eztags');
        $processor->mapConfigArray('tag_view_match', $config, ContextualizerInterface::MERGE_FROM_SECOND_LEVEL);
    }

    /**
     * Allow an extension to prepend the extension configurations.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $configFile = __DIR__ . '/../Resources/config/ezpublish.yml';
        $config = Yaml::parse(file_get_contents($configFile));
        $container->prependExtensionConfig('ezpublish', $config);
        $container->addResource(new FileResource($configFile));
    }
}
