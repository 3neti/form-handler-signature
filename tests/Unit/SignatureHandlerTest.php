<?php

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use LBHurtado\FormFlowManager\Contracts\FormHandlerInterface;
use LBHurtado\FormFlowManager\Contracts\FormHandlerPreviewInterface;
use LBHurtado\FormFlowManager\Data\FormFlowStepData;
use LBHurtado\FormHandlerSignature\SignatureHandler;

test('implements FormHandlerInterface', function () {
    $handler = new SignatureHandler;
    expect($handler)->toBeInstanceOf(FormHandlerInterface::class);
});

test('signature preview uses the production screen in inert mode', function () {
    $preview = (new SignatureHandler)->preview(
        FormFlowStepData::from(['handler' => 'signature', 'config' => []]),
    );

    expect(new SignatureHandler)->toBeInstanceOf(FormHandlerPreviewInterface::class)
        ->and($preview['component'])->toBe('form-flow/signature/SignatureCapturePage')
        ->and($preview['props']['preview_mode'])->toBeTrue();
});

test('returns correct handler name', function () {
    $handler = new SignatureHandler;
    expect($handler->getName())->toBe('signature');
});

test('validates required signature field', function () {
    $handler = new SignatureHandler;
    $request = Request::create('/test', 'POST', [
        'data' => [],
    ]);
    $step = FormFlowStepData::from(['handler' => 'signature', 'config' => []]);

    expect(fn () => $handler->handle($request, $step))
        ->toThrow(ValidationException::class);
});

test('validates signature format as string', function () {
    $handler = new SignatureHandler;
    $request = Request::create('/test', 'POST', [
        'data' => [
            'signature' => 12345, // Invalid: not a string
        ],
    ]);
    $step = FormFlowStepData::from(['handler' => 'signature', 'config' => []]);

    expect(fn () => $handler->handle($request, $step))
        ->toThrow(ValidationException::class);
});

test('accepts valid base64 image', function () {
    $handler = new SignatureHandler;
    $request = Request::create('/test', 'POST', [
        'data' => [
            'signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            'width' => 600,
            'height' => 256,
            'format' => 'image/png',
        ],
    ]);
    $step = FormFlowStepData::from(['handler' => 'signature', 'config' => []]);

    $result = $handler->handle($request, $step);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('signature')
        ->and($result)->toHaveKey('timestamp');
});

test('validates width constraints', function () {
    $handler = new SignatureHandler;
    $request = Request::create('/test', 'POST', [
        'data' => [
            'signature' => 'data:image/png;base64,test',
            'width' => 50, // Too small (min: 100)
        ],
    ]);
    $step = FormFlowStepData::from(['handler' => 'signature', 'config' => []]);

    expect(fn () => $handler->handle($request, $step))
        ->toThrow(ValidationException::class);
});

test('validates height constraints', function () {
    $handler = new SignatureHandler;
    $request = Request::create('/test', 'POST', [
        'data' => [
            'signature' => 'data:image/png;base64,test',
            'height' => 1500, // Too large (max: 1000)
        ],
    ]);
    $step = FormFlowStepData::from(['handler' => 'signature', 'config' => []]);

    expect(fn () => $handler->handle($request, $step))
        ->toThrow(ValidationException::class);
});

test('accepts valid image formats', function ($format) {
    $handler = new SignatureHandler;
    $request = Request::create('/test', 'POST', [
        'data' => [
            'signature' => "data:{$format};base64,test",
            'format' => $format,
        ],
    ]);
    $step = FormFlowStepData::from(['handler' => 'signature', 'config' => []]);

    $result = $handler->handle($request, $step);

    expect($result['format'])->toBe($format);
})->with(['image/png', 'image/jpeg', 'image/webp']);

test('rejects invalid image formats', function () {
    $handler = new SignatureHandler;
    $request = Request::create('/test', 'POST', [
        'data' => [
            'signature' => 'data:image/gif;base64,test',
            'format' => 'image/gif',
        ],
    ]);
    $step = FormFlowStepData::from(['handler' => 'signature', 'config' => []]);

    expect(fn () => $handler->handle($request, $step))
        ->toThrow(ValidationException::class);
});

test('creates SignatureData with timestamp', function () {
    $handler = new SignatureHandler;
    $request = Request::create('/test', 'POST', [
        'data' => [
            'signature' => 'data:image/png;base64,test',
            'width' => 600,
            'height' => 256,
            'format' => 'image/png',
        ],
    ]);
    $step = FormFlowStepData::from(['handler' => 'signature', 'config' => []]);

    $result = $handler->handle($request, $step);

    expect($result['timestamp'])->toBeString()
        ->and($result['width'])->toBe(600)
        ->and($result['height'])->toBe(256)
        ->and($result['format'])->toBe('image/png');
});

test('uses config defaults when values not provided', function () {
    $handler = new SignatureHandler;
    $request = Request::create('/test', 'POST', [
        'data' => [
            'signature' => 'data:image/png;base64,test',
            // No width, height, format provided
        ],
    ]);
    $step = FormFlowStepData::from(['handler' => 'signature', 'config' => []]);

    $result = $handler->handle($request, $step);

    expect($result['width'])->toBe(600) // Default from config
        ->and($result['height'])->toBe(256)
        ->and($result['format'])->toBe('image/png');
});

test('config schema validation includes drawing properties', function () {
    $handler = new SignatureHandler;
    $schema = $handler->getConfigSchema();

    expect($schema)->toHaveKey('width')
        ->and($schema)->toHaveKey('height')
        ->and($schema)->toHaveKey('quality')
        ->and($schema)->toHaveKey('format')
        ->and($schema)->toHaveKey('line_width')
        ->and($schema)->toHaveKey('line_color')
        ->and($schema)->toHaveKey('line_cap')
        ->and($schema)->toHaveKey('line_join');
});

test('handler auto-registers with form-flow-manager', function () {
    $handlers = config('form-flow.handlers', []);

    expect($handlers)->toHaveKey('signature')
        ->and($handlers['signature'])->toBe(SignatureHandler::class);
});

test('validates line_width in config schema', function () {
    $handler = new SignatureHandler;
    $schema = $handler->getConfigSchema();

    expect($schema['line_width'])->toContain('min:1')
        ->and($schema['line_width'])->toContain('max:10');
});

test('validates line_cap options in config schema', function () {
    $handler = new SignatureHandler;
    $schema = $handler->getConfigSchema();

    expect($schema['line_cap'])->toContain('butt')
        ->and($schema['line_cap'])->toContain('round')
        ->and($schema['line_cap'])->toContain('square');
});
test('published claim screen fails closed in preview mode', function () {
    $source = file_get_contents(dirname(__DIR__, 2).'/stubs/resources/js/pages/form-flow/signature/SignatureCapturePage.vue');

    expect($source)->toContain('props.preview_mode');
});
