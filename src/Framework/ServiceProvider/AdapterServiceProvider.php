<?php

namespace Survey54\Reap\Framework\ServiceProvider;

use AfricasTalking\SDK\AfricasTalking;
use GuzzleHttp\Client;
use Slim\Container;
use Survey54\Library\Adapter\CloudinaryAdapter;
use Survey54\Library\Adapter\IpToCountryAdapter;
use Survey54\Library\Adapter\SwiftMailerAdapter;
use Survey54\Library\Adapter\TextLocalAdapter;
use Survey54\Library\Exception\ErrorHandler;
use Survey54\Reap\Framework\Adapter\AfricaTalkingAdapter;
use Survey54\Reap\Framework\Adapter\AirtimeAdapter;

class AdapterServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     */
    public function register(Container $container): void
    {
        $container['errorHandler'] = fn() => new ErrorHandler($container);

        $container[AfricaTalkingAdapter::class] = fn() => new AfricaTalkingAdapter(
            new AfricasTalking($_SERVER['AFRICASTALKING_USERNAME'], $_SERVER['AFRICASTALKING_APIKEY'])
        );

        $container[AirtimeAdapter::class] = fn() => new AirtimeAdapter(
            new Client(['base_uri' => 'https://api.engagespark.com']),
            $_SERVER['ENGAGE_SPARK_TOKEN'],
        );

        $container[CloudinaryAdapter::class] = fn() => new CloudinaryAdapter(
            $container['config.cloudinary'],
        );

        $container[IpToCountryAdapter::class] = fn() => new IpToCountryAdapter(
            new Client(['base_uri' => 'http://www.geoplugin.net'])
        );

        $container[SwiftMailerAdapter::class] = fn() => new SwiftMailerAdapter(
            $container['config.swift'],
        );

        $container[TextLocalAdapter::class] = fn() => new TextLocalAdapter(
            $_SERVER['TEXT_LOCAL_API_KEY'],
        );
    }
}
