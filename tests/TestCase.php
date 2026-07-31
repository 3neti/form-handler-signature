<?php

declare(strict_types=1);

namespace LBHurtado\FormHandlerSignature\Tests;

use LBHurtado\FormHandlerSignature\SignatureHandlerServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Spatie\LaravelData\LaravelDataServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            LaravelDataServiceProvider::class,
            SignatureHandlerServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('database.default', 'testing');
        $app['config']->set('inertia.testing.ensure_pages_exist', false);

        // Laravel Data configuration
        $app['config']->set('data.validation_strategy', 'only_requests');
        $app['config']->set('data.max_transformation_depth', 6);
        $app['config']->set('data.throw_when_max_transformation_depth_reached', 6);

        // Signature handler configuration
        $app['config']->set('signature-handler.width', 600);
        $app['config']->set('signature-handler.height', 256);
        $app['config']->set('signature-handler.quality', 0.85);
        $app['config']->set('signature-handler.format', 'image/png');
        $app['config']->set('signature-handler.line_width', 2);
        $app['config']->set('signature-handler.line_color', '#000000');
        $app['config']->set('signature-handler.line_cap', 'round');
        $app['config']->set('signature-handler.line_join', 'round');
    }
}
