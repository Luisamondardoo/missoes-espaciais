<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);

$missoes = [
    ["id" => 1, "nome" => "Apollo 11", "ano" => 1969, "agencia" => "NASA", "status" => "Concluída"],
    ["id" => 2, "nome" => "Voyager 1", "ano" => 1977, "agencia" => "NASA", "status" => "Em operação"],
    ["id" => 3, "nome" => "Artemis II", "ano" => 2026, "agencia" => "NASA", "status" => "Planejada"],
    ["id" => 4, "nome" => "Sputnik 1", "ano" => 1957, "agencia" => "URSS", "status" => "Concluída"],
    ["id" => 5, "nome" => "Mars Rover Perseverance", "ano" => 2020, "agencia" => "NASA", "status" => "Em operação"]
];

function jsonResponse(Response $response, $data, int $status = 200): Response
{
    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    return $response
        ->withHeader('Content-Type', 'application/json; charset=utf-8')
        ->withStatus($status);
}

$app->get('/status', function (Request $request, Response $response) {
    return jsonResponse($response, ["status" => "ok"], 200);
});

$app->get('/missoes', function (Request $request, Response $response) use ($missoes) {
    return jsonResponse($response, array_values($missoes), 200);
});

$app->get('/missoes/{id}', function (Request $request, Response $response, array $args) use ($missoes) {
    $id = (int) $args['id'];
    foreach ($missoes as $missao) {
        if ($missao['id'] === $id) {
            return jsonResponse($response, $missao, 200);
        }
    }
    return jsonResponse($response, ["erro" => "Missão não encontrada"], 404);
});

$app->post('/missoes', function (Request $request, Response $response) use (&$missoes) {
    $dados = $request->getParsedBody();

    if (empty($dados['nome']) || empty($dados['ano']) || empty($dados['agencia']) || empty($dados['status'])) {
        return jsonResponse($response, ["erro" => "Campos obrigatórios: nome, ano, agencia, status"], 400);
    }

    $novoId = empty($missoes) ? 1 : (max(array_column($missoes, 'id')) + 1);

    $novaMissao = [
        "id"      => $novoId,
        "nome"    => $dados['nome'],
        "ano"     => (int) $dados['ano'],
        "agencia" => $dados['agencia'],
        "status"  => $dados['status']
    ];

    $missoes[] = $novaMissao;
    return jsonResponse($response, $novaMissao, 201);
});

$app->put('/missoes/{id}', function (Request $request, Response $response, array $args) use (&$missoes) {
    $id = (int) $args['id'];
    $dados = $request->getParsedBody();

    foreach ($missoes as $index => $missao) {
        if ($missao['id'] === $id) {
            if (isset($dados['nome']))    $missoes[$index]['nome']    = $dados['nome'];
            if (isset($dados['ano']))     $missoes[$index]['ano']     = (int) $dados['ano'];
            if (isset($dados['agencia'])) $missoes[$index]['agencia'] = $dados['agencia'];
            if (isset($dados['status']))  $missoes[$index]['status']  = $dados['status'];
            return jsonResponse($response, $missoes[$index], 200);
        }
    }
    return jsonResponse($response, ["erro" => "Missão não encontrada"], 404);
});

$app->delete('/missoes/{id}', function (Request $request, Response $response, array $args) use (&$missoes) {
    $id = (int) $args['id'];
    foreach ($missoes as $index => $missao) {
        if ($missao['id'] === $id) {
            array_splice($missoes, $index, 1);
            return $response->withStatus(204);
        }
    }
    return jsonResponse($response, ["erro" => "Missão não encontrada"], 404);
});

$app->run();