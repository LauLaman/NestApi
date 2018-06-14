<?php

declare(strict_types=1);

namespace LauLamanApps\NestApi;

use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use LauLamanApps\NestApi\Client\Device\Factory\Camera\EventFactory;
use LauLamanApps\NestApi\Client\Device\Factory\CameraFactory;
use LauLamanApps\NestApi\Client\Device\Factory\ProtectFactory;
use LauLamanApps\NestApi\Client\Device\Factory\ThermostatFactory;
use LauLamanApps\NestApi\Client\Factory\StructureFactory;
use LauLamanApps\NestApi\Http\Adapter\Guzzle\Client as GuzzleClient;
use LauLamanApps\NestApi\Http\ClientInterface;
use LauLamanApps\NestApi\Http\Endpoint\Production;
use Psr\Http\Message\RequestInterface;

final class NestClientFactory
{
    public static function create(string $accessToken): NestClient
    {
        return new NestClient(
            self::createHttpClient($accessToken),
            new ThermostatFactory(),
            new ProtectFactory(),
            new CameraFactory(
                new EventFactory()
            ),
            new StructureFactory()
        );
    }

    private static function createHttpClient(string $accessToken): ClientInterface
    {
        $handler = new HandlerStack();
        $handler->setHandler(new CurlHandler());

        $handler->push(Middleware::mapRequest(function (RequestInterface $request) use ($accessToken) {
            return $request->withHeader('Authorization', 'Bearer ' . $accessToken);
        }));

        return new GuzzleClient(
            new \GuzzleHttp\Client(['handler' => $handler]),
            new Production()
        );
    }
}