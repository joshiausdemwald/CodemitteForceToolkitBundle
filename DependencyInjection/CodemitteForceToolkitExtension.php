<?php
/**
 * Copyright (C) 2012 code mitte GmbH - Zeughausstr. 28-38 - 50667 Cologne/Germany
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in the
 * Software without restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the
 * Software, and to permit persons to whom the Software is furnished to do so, subject
 * to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Codemitte\Bundle\ForceToolkitBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;

/**
 * CodemitteForceToolkitExtension
 *
 * @author Johannes Heinen <johannes.heinen@code-mitte.de>
 * @copyright 2012 code mitte GmbH, Cologne, Germany
 * @package Bundle
 * @subpackage ForceToolkitBundle
 */
class CodemitteForceToolkitExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $processor     = new Processor();
        $configuration = new Configuration();

        $config = $processor->process($configuration->getConfigTree(), $configs);

        $container->setParameter('codemitte_forcetk.metadata.cache.ttl', $config['metadata']['cache_ttl']);
        $container->setParameter('codemitte_forcetk.metadata.cache.service_id', $config['metadata']['cache_service_id']);
        $container->setParameter('codemitte_forcetk.metadata.cache.location', $config['metadata']['cache_location']);

        $container->setParameter('codemitte_forcetk.client.class', $config['soap_api_client']['classname']);
        $container->setParameter('codemitte_forcetk.client.default_api_user', $config['soap_api_client']['api_users']['default']);
        $container->setParameter('codemitte_forcetk.client.api_users', $config['soap_api_client']['api_users']['locales']);
        $container->setParameter('codemitte_forcetk.client.service_location', $config['soap_api_client']['service_location']);
        $container->setParameter('codemitte_forcetk.client.wsdl_location', $config['soap_api_client']['wsdl_location']);
        $container->setParameter('codemitte_forcetk.client.connection_ttl', $config['soap_api_client']['connection_ttl']);

        $loader->load('services.xml');

        if(isset($config['metadata']['cache_service_id']))
        {
            $container->setAlias('codemitte_forcetk.metadata.cache', $config['metadata']['cache_service_id']);
        }
    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/schema';
    }

    public function getNamespace()
    {
        return 'http://code-mitte.de/schema/dic/forcetk';
    }
}
