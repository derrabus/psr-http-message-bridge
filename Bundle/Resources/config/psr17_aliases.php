<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->alias('psr_http_message.psr.request_factory', RequestFactoryInterface::class)
        ->alias('psr_http_message.psr.response_factory', ResponseFactoryInterface::class)
        ->alias('psr_http_message.psr.server_request_factory', ServerRequestFactoryInterface::class)
        ->alias('psr_http_message.psr.stream_factory', StreamFactoryInterface::class)
        ->alias('psr_http_message.psr.uploaded_file_factory', UploadedFileFactoryInterface::class)
        ->alias('psr_http_message.psr.uri_factory', UriFactoryInterface::class)
    ;
};
