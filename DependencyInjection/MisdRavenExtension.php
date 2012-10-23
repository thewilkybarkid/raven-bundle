<?php

/*
 * This file is part of the MisdRavenBundle for Symfony2.
 *
 * (c) University of Cambridge
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Misd\RavenBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * MisdRavenExtension.
 *
 * @author Chris Wilkinson <chris.wilkinson@admin.cam.ac.uk>
 */
class MisdRavenExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(array(__DIR__ . '/../Resources/config/')));
        $loader->load('services.yml');

        $container->setParameter(
            'misd_raven.description',
            isset($config['description']) ? $config['description'] : null
        );
        $container->setParameter('misd_raven.test_service', $config['use_test_service']);
        $container->setParameter(
            'raven_service.class',
            true === $container->getParameter('misd_raven.test_service') ? $container->getParameter(
                'raven_test_service.class'
            ) : $container->getParameter(
                'raven_live_service.class'
            )
        );
    }
}