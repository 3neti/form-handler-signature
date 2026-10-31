<?php

use LBHurtado\FormFlowManager\Services\FormFlowService;

test('a rejected signature can be retried and advances the flow', function () {
    $this->postJson('/form-flow/start', [
        'reference_id' => 'signature-flow-test',
        'steps' => [['handler' => 'signature'], ['handler' => 'form']],
        'callbacks' => ['on_complete' => 'https://example.com/complete'],
    ])->assertSuccessful();

    $flowId = app(FormFlowService::class)->getFlowStateByReference('signature-flow-test')['flow_id'];
    $data = ['signature' => 'data:image/png;base64,test', 'width' => 1206, 'height' => 4097];

    $this->postJson("/form-flow/{$flowId}/step/0", ['data' => $data])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['height']);
    expect(app(FormFlowService::class)->getFlowState($flowId)['current_step'])->toBe(0);

    $data['height'] = 1131;
    $this->postJson("/form-flow/{$flowId}/step/0", ['data' => $data])
        ->assertSuccessful()
        ->assertJsonPath('next_step', 1);
    expect(app(FormFlowService::class)->getFlowState($flowId)['current_step'])->toBe(1);
});
