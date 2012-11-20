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

/**
 * Configuration
 *
 * @author Johannes Heinen <johannes.heinen@code-mitte.de>
 * @copyright 2012 code mitte GmbH, Cologne, Germany
 * @package Bundle
 * @subpackage ForceToolkitBundle
 */
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * Configuration structure.
 *
 * @author Johannes Heinen <johannes.heinen@code-mitte.de>
 */
class Configuration
{
    /**
     * Generates the configuration tree.
     *
     * @return Symfony\Component\Config\Definition\NodeInterface
     */
    public function getConfigTree()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('codemitte_force_toolkit', 'array');

        $rootNode
            ->children()
                ->arrayNode('soap_api_client')
                    ->isRequired()
                    ->children()
                        ->scalarNode('classname')->defaultValue('Codemitte\\ForceToolkit\\Soap\\Client\\PartnerClient')->end()
                        ->scalarNode('connection_ttl')->defaultValue(28800)->end()
                        ->scalarNode('service_location')->defaultNull()->end()
                        ->scalarNode('wsdl_location')->isRequired()->end()
                        ->arrayNode('api_users')
                            ->isRequired()
                            ->children()
                                ->arrayNode('default')
                                    ->isRequired()
                                    ->children()
                                        ->scalarNode('username')->end()
                                        ->scalarNode('password')->end()
                                    ->end()
                                ->end()
                                ->arrayNode('locales')
                                    ->isRequired()
                                    ->useAttributeAsKey('locale')
                                    ->prototype('array')
                                        ->children()
                                            ->scalarNode('locale')->end()
                                            ->scalarNode('username')->end()
                                            ->scalarNode('password')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('metadata')
                    ->isRequired()
                    ->children()
                        ->scalarNode('cache_service_id')->isRequired()->end()
                        ->scalarNode('cache_location')->defaultValue('%kernel.root_dir%/cache/forcetk')->end()
                        ->scalarNode('cache_ttl')->defaultValue(-1)->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder->buildTree();
    }
}
