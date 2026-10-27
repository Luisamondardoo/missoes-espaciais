
<?php

require __DIR__ . '/vendor/autoload.php';

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;

$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$missoes = [
    ['id' => 1, 'nome' => 'Apollo 11', 'ano' => 1969, 'agencia' => 'NASA', 'status' => 'Concluída'],
    ['id' => 2, 'nome' => 'Voyager 1', 'ano' => 1977, 'agencia' => 'NASA', 'status' => 'Em operação'],
    ['id' => 3, 'nome' => 'Artemis II', 'ano' => 2026, 'agencia' => 'NASA', 'status' => 'Planejada'],
    ['id' => 4, 'nome' => 'Sputnik 1', 'ano' => 1957, 'agencia' => 'URSS', 'status' => 'Concluída'],
    ['id' => 5, 'nome' => 'Mars Rover Perseverance', 'ano' => 2020, 'agencia' => 'NASA', 'status' => 'Em operação']
];

$responderJson = function (
    ResponseInterface $response,
    $dados,
    int $status = 200
): ResponseInterface {
    $response->getBody()->write(
        json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
    );

    return $response
        ->withHeader('Content-Type', 'application/json; charset=utf-8')
        ->withStatus($status);
};

$validarDadosMissao = function ($dados): bool {
    return is_array($dados)
        && isset($dados['nome'], $dados['ano'], $dados['agencia'], $dados['status'])
        && is_string($dados['nome'])
        && trim($dados['nome']) !== ''
        && is_numeric($dados['ano'])
        && (int) $dados['ano'] > 0
        && is_string($dados['agencia'])
        && trim($dados['agencia']) !== ''
        && is_string($dados['status'])
        && trim($dados['status']) !== '';
};

$app->get('/status', function (
    ServerRequestInterface $request,
    ResponseInterface $response
) use ($responderJson) {
    return $responderJson($response, [
        'status' => 'ok',
        'mensagem' => 'API de missões espaciais funcionando!'
    ]);
});

$app->get('/missoes', function (
    ServerRequestInterface $request,
    ResponseInterface $response
) use (&$missoes, $responderJson) {
    return $responderJson($response, array_values($missoes));
});

$app->get('/missoes/{id}', function (
    ServerRequestInterface $request,
    ResponseInterface $response,
    array $args
) use (&$missoes, $responderJson) {
    $idBuscado = (int) $args['id'];

    foreach ($missoes as $missaoAtual) {
        if ($missaoAtual['id'] === $idBuscado) {
            return $responderJson($response, $missaoAtual);
        }
    }

    return $responderJson($response, ['erro' => 'Missão não encontrada.'], 404);
});

$app->post('/missoes', function (
    ServerRequestInterface $request,
    ResponseInterface $response
) use (&$missoes, $responderJson, $validarDadosMissao) {
    $novosDados = $request->getParsedBody();

    if (!$validarDadosMissao($novosDados)) {
        return $responderJson($response, [
            'erro' => 'Informe nome, ano, agencia e status corretamente.'
        ], 400);
    }

    $novoId = empty($missoes) ? 1 : max(array_column($missoes, 'id')) + 1;

    $missaoCriada = [
        'id' => $novoId,
        'nome' => trim($novosDados['nome']),
        'ano' => (int) $novosDados['ano'],
        'agencia' => trim($novosDados['agencia']),
        'status' => trim($novosDados['status'])
    ];

    $missoes[] = $missaoCriada;

    return $responderJson($response, $missaoCriada, 201);
});

$app->put('/missoes/{id}', function (
    ServerRequestInterface $request,
    ResponseInterface $response,
    array $args
) use (&$missoes, $responderJson, $validarDadosMissao) {
    $idAtualizar = (int) $args['id'];
    $dadosAtualizados = $request->getParsedBody();

    if (!$validarDadosMissao($dadosAtualizados)) {
        return $responderJson($response, [
            'erro' => 'Informe nome, ano, agencia e status corretamente.'
        ], 400);
    }

    foreach ($missoes as $posicao => $missaoAtual) {
        if ($missaoAtual['id'] === $idAtualizar) {
            $missoes[$posicao] = [
                'id' => $idAtualizar,
                'nome' => trim($dadosAtualizados['nome']),
                'ano' => (int) $dadosAtualizados['ano'],
                'agencia' => trim($dadosAtualizados['agencia']),
                'status' => trim($dadosAtualizados['status'])
            ];

            return $responderJson($response, $missoes[$posicao]);
        }
    }

    return $responderJson($response, ['erro' => 'Missão não encontrada.'], 404);
});

$app->delete('/missoes/{id}', function (
    ServerRequestInterface $request,
    ResponseInterface $response,
    array $args
) use (&$missoes, $responderJson) {
    $idExcluir = (int) $args['id'];

    foreach ($missoes as $posicao => $missaoAtual) {
        if ($missaoAtual['id'] === $idExcluir) {
            array_splice($missoes, $posicao, 1);

            return $responderJson($response, [
                'mensagem' => 'Missão excluída com sucesso.'
            ]);
        }
    }

    return $responderJson($response, ['erro' => 'Missão não encontrada.'], 404);
});

if (PHP_SAPI !== 'cli' || !defined('PHPUNIT_COMPOSER_INSTALL')) {
    $app->run();
}

return $app;
