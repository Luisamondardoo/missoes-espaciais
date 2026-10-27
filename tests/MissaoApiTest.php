
<?php

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;

class MissaoApiTest extends TestCase
{
    private function montarAplicacao()
    {
        return require __DIR__ . '/../index.php';
    }

    private function montarRequisicao(
        string $metodoHttp,
        string $rota,
        ?array $conteudo = null
    ) {
        $fabricaRequisicao = new ServerRequestFactory();
        $requisicao = $fabricaRequisicao->createServerRequest($metodoHttp, $rota);

        if ($conteudo !== null) {
            $fabricaStream = new StreamFactory();
            $corpoJson = $fabricaStream->createStream(
                json_encode($conteudo, JSON_UNESCAPED_UNICODE)
            );

            $requisicao = $requisicao
                ->withHeader('Content-Type', 'application/json')
                ->withBody($corpoJson);
        }

        return $requisicao;
    }

    private function obterRespostaJson($resposta): array
    {
        return json_decode((string) $resposta->getBody(), true);
    }

    public function testStatusDaApi()
    {
        $app = $this->montarAplicacao();
        $resposta = $app->handle($this->montarRequisicao('GET', '/status'));
        $dados = $this->obterRespostaJson($resposta);

        $this->assertEquals(200, $resposta->getStatusCode());
        $this->assertEquals('ok', $dados['status']);
    }

    public function testListarMissoes()
    {
        $app = $this->montarAplicacao();
        $resposta = $app->handle($this->montarRequisicao('GET', '/missoes'));
        $dados = $this->obterRespostaJson($resposta);

        $this->assertEquals(200, $resposta->getStatusCode());
        $this->assertIsArray($dados);
        $this->assertCount(5, $dados);
    }

    public function testBuscarMissaoExistente()
    {
        $app = $this->montarAplicacao();
        $resposta = $app->handle($this->montarRequisicao('GET', '/missoes/1'));
        $dados = $this->obterRespostaJson($resposta);

        $this->assertEquals(200, $resposta->getStatusCode());
        $this->assertEquals(1, $dados['id']);
        $this->assertEquals('Apollo 11', $dados['nome']);
    }

    public function testBuscarMissaoInexistente()
    {
        $app = $this->montarAplicacao();
        $resposta = $app->handle($this->montarRequisicao('GET', '/missoes/999'));

        $this->assertEquals(404, $resposta->getStatusCode());
    }

    public function testCadastrarMissao()
    {
        $app = $this->montarAplicacao();

        $conteudo = [
            'nome' => 'Missão Teste',
            'ano' => 2030,
            'agencia' => 'NASA',
            'status' => 'Planejada'
        ];

        $resposta = $app->handle(
            $this->montarRequisicao('POST', '/missoes', $conteudo)
        );
        $dados = $this->obterRespostaJson($resposta);

        $this->assertEquals(201, $resposta->getStatusCode());
        $this->assertEquals('Missão Teste', $dados['nome']);
        $this->assertArrayHasKey('id', $dados);
    }

    public function testCadastrarMissaoSemCamposObrigatorios()
    {
        $app = $this->montarAplicacao();

        $resposta = $app->handle(
            $this->montarRequisicao('POST', '/missoes', [
                'nome' => 'Missão incompleta'
            ])
        );

        $this->assertEquals(400, $resposta->getStatusCode());
    }

    public function testAtualizarMissao()
    {
        $app = $this->montarAplicacao();

        $conteudo = [
            'nome' => 'Apollo 11 Atualizada',
            'ano' => 1969,
            'agencia' => 'NASA',
            'status' => 'Concluída'
        ];

        $resposta = $app->handle(
            $this->montarRequisicao('PUT', '/missoes/1', $conteudo)
        );
        $dados = $this->obterRespostaJson($resposta);

        $this->assertEquals(200, $resposta->getStatusCode());
        $this->assertEquals('Apollo 11 Atualizada', $dados['nome']);
    }

    public function testAtualizarMissaoInexistente()
    {
        $app = $this->montarAplicacao();

        $conteudo = [
            'nome' => 'Missão Teste',
            'ano' => 2030,
            'agencia' => 'NASA',
            'status' => 'Planejada'
        ];

        $resposta = $app->handle(
            $this->montarRequisicao('PUT', '/missoes/999', $conteudo)
        );

        $this->assertEquals(404, $resposta->getStatusCode());
    }

    public function testExcluirMissao()
    {
        $app = $this->montarAplicacao();

        $respostaExcluir = $app->handle(
            $this->montarRequisicao('DELETE', '/missoes/1')
        );

        $this->assertEquals(200, $respostaExcluir->getStatusCode());

        $respostaBusca = $app->handle(
            $this->montarRequisicao('GET', '/missoes/1')
        );

        $this->assertEquals(404, $respostaBusca->getStatusCode());
    }

    public function testExcluirMissaoInexistente()
    {
        $app = $this->montarAplicacao();

        $resposta = $app->handle(
            $this->montarRequisicao('DELETE', '/missoes/999')
        );

        $this->assertEquals(404, $resposta->getStatusCode());
    }
}
