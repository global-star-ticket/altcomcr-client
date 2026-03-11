<?php

beforeAll(function () {
    validateCredentials();
});

beforeEach(function () {
    skipIfInvalidCredentials();
});

test('consultar saldo', function () {
    $response = altcom()
        ->consultas()
        ->saldo();

    expect($response->success)->toBeTrue()
        ->and($response->respuesta)->toBe('Ok')
        ->and($response->data)->toHaveKey('vence')
        ->and($response->data)->toHaveKey('dias');
})->group('integration');

test('consultar llave', function () {
    $response = altcom()
        ->consultas()
        ->llave();

    expect($response->success)->toBeTrue()
        ->and($response->respuesta)->toBe('Ok')
        ->and($response->data)->toHaveKey('vence')
        ->and($response->data)->toHaveKey('actividades')
        ->and($response->data['actividades'])->toBeArray();
})->group('integration');
