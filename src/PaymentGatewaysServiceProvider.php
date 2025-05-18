<?php
namespace Kmalarifi97\PaymentGateways;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use Kmalarifi97\PaymentGateways\Contracts\PaymentGateway;
use Kmalarifi97\PaymentGateways\Contracts\PaymentGatewayDetector;
use Kmalarifi97\PaymentGateways\Exceptions\InvalidGatewayException;
use Kmalarifi97\PaymentGateways\Gateways\HyperpayGateway;
use Kmalarifi97\PaymentGateways\Gateways\FatoorahGateway;

class PaymentGatewaysServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /* 1. Config */
        $this->mergeConfigFrom(__DIR__.'/../config/payment-gateways.php', 'payment-gateways');

        /* 2. Detector */
        $this->app->singleton(PaymentGatewayDetector::class, fn ($app) =>
        $app->make(config('payment-gateways.detector'))
        );

        /* 3-A. Binding for the *single* (detect) gateway */
        $this->app->bind(PaymentGateway::class, function ($app) {
            $detector = $app->make(PaymentGatewayDetector::class);
            $alias    = $detector->detect($app->make(Request::class));
            return $this->makeGateway($alias);
        });

        $this->app->bind('payment-gateways.list', function ($app) {
            $detector = $app->make(PaymentGatewayDetector::class);
            $aliases  = $detector->detectAll($app->make(Request::class));

            $instances = [];
            foreach ($aliases as $alias) {
                $instances[$alias] = $this->makeGateway($alias);
            }
            return $instances;              // e.g. ['hyperpay' => …, 'fatoorah' => …]
        });
    }

    /* Helper that converts an alias to a concrete instance */
    private function makeGateway(string $alias)
    {
        return match ($alias) {
            'hyperpay' => new HyperpayGateway(
                config('payment-gateways.credentials.hyperpay.accessToken')
            ),
            'fatoorah' => new FatoorahGateway(
                config('payment-gateways.credentials.fatoorah.token')
            ),
            default    => throw new InvalidGatewayException("Unknown gateway [$alias]"),
        };
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/payment-gateways.php' => config_path('payment-gateways.php'),
        ], 'payment-gateways-config');
    }
}
