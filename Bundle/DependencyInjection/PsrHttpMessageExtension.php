<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\PsrHttpMessage\Bundle\DependencyInjection;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * @author Alexander M. Turek <me@derrabus.de>
 */
final class PsrHttpMessageExtension extends Extension implements CompilerPassInterface
{
    private $messageFactoriesImplementation = false;

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(\dirname(__DIR__).'/Resources/config'));
        $loader->load('http_foundation.php');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('psr_http_message.response_buffer', $config['response_buffer']);

        if ($config['message_converters']['enabled']) {
            $loader->load('message_converters.php');
        }

        $this->messageFactoriesImplementation = $config['message_factories']['enabled'] ? $config['message_factories']['implementation'] : 'disabled';
        switch ($this->messageFactoriesImplementation) {
            case 'nyholm':
                $loader->load('nyholm.php');
                $loader->load('psr_factories.php');
                break;
            case 'diactoros':
                $loader->load('diactoros.php');
                $loader->load('psr_factories.php');
                break;
            case 'custom':
                $loader->load('psr17_aliases.php');
                $loader->load('psr_factories.php');
                break;
            default:
                $container->removeDefinition('psr_http_message.server_request_resolver');
                break;
        }
    }

    public function process(ContainerBuilder $container): void
    {
        $this->processPsr17FactoriesAliases($container);
    }

    private function processPsr17FactoriesAliases(ContainerBuilder $container): void
    {
        if ('disabled' === $this->messageFactoriesImplementation) {
            return;
        }

        $psr17Interfaces = [
            RequestFactoryInterface::class => 'psr_http_message.psr.request_factory',
            ResponseFactoryInterface::class => 'psr_http_message.psr.response_factory',
            ServerRequestFactoryInterface::class => 'psr_http_message.psr.server_request_factory',
            StreamFactoryInterface::class => 'psr_http_message.psr.stream_factory',
            UploadedFileFactoryInterface::class => 'psr_http_message.psr.uploaded_file_factory',
            UriFactoryInterface::class => 'psr_http_message.psr.uri_factory',
        ];

        foreach ($psr17Interfaces as $interface => $service) {
            if (!$container->has($interface) && 'custom' !== $this->messageFactoriesImplementation) {
                $container->setAlias($interface, $service);
            }
        }
    }
}
