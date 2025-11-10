# Documentação - IntegradorSmartecService

O `IntegradorSmartecService` é um service modernizado para integração com a API da Smartec, seguindo as melhores práticas do Laravel.

## Configuração

### 1. Variáveis de Ambiente

Adicione as seguintes variáveis ao seu arquivo `.env`:

```env
SMARTEC_TOKEN=seu_token_aqui
SMARTEC_BASE_URL=https://sistema.smartec.com.br/api
SMARTEC_TIMEOUT=30
```

### 2. Configuração dos Serviços

A configuração é automaticamente carregada do arquivo `config/services.php`:

```php
'smartec' => [
    'base_url' => env('SMARTEC_BASE_URL', 'https://sistema.smartec.com.br/api'),
    'token' => env('SMARTEC_TOKEN'),
    'timeout' => env('SMARTEC_TIMEOUT', 30),
],
```

## Uso do Service

### Injeção de Dependência

```php
use App\Services\IntegradorSmartecService;

class SeuController extends Controller
{
    public function __construct(
        private IntegradorSmartecService $smartecService
    ) {}

    public function consultarVeiculo(Request $request)
    {
        $resultado = $this->smartecService->consultarVeiculo(
            placa: $request->placa,
            uf: $request->uf,
            frota: $request->frota,
            prefixo: $request->prefixo,
            cnpjCpf: $request->cnpj_cpf,
            dataBase: $request->data_base,
            renavam: $request->renavam,
            tipo: $request->tipo
        );

        return response()->json($resultado);
    }
}
```

### Instanciação Direta

```php
use App\Services\IntegradorSmartecService;

$smartecService = new IntegradorSmartecService();
```

## Métodos Disponíveis

### 1. Consultar Veículo

```php
$resultado = $smartecService->consultarVeiculo(
    placa: 'ABC1234',
    uf: 'SP',
    frota: '001',
    prefixo: 'VEI',
    cnpjCpf: '12345678000123',
    dataBase: '2024-01-01',
    renavam: '123456789',
    tipo: 'veiculo'
);
```

### 2. Indicar Infração

```php
$resultado = $smartecService->indicarInfracao(
    nome: 'João Silva',
    cnh: '12345678901',
    tipo: 'indicacao',
    ait: '123456',
    codigoOrgao: '001' // opcional
);
```

### 3. Excluir Indicação

```php
$resultado = $smartecService->excluirIndicacao(
    tipo: 'exclusao',
    ait: '123456'
);
```

### 4. Cadastrar CNH

```php
$dadosCnh = [
    'Tipo' => 'cadastro',
    'Cpf' => '12345678901',
    'Cnh' => '12345678901',
    'Uf' => 'SP',
    'Validade' => '2030-12-31',
    'Cedula' => '123456',
    'DataNascimento' => '1990-01-01',
    'Data1Habilitacao' => '2008-01-01',
    'Rg' => '123456789',
    'Nome' => 'João Silva',
    'UfNascimento' => 'SP',
    'MunicipioNascimento' => 'São Paulo',
    'RenaCh' => '123456789',
    'Municipio' => 'São Paulo',
    'CodigoSeguranca' => '123',
    'Categoria' => 'B',
    'Grupo' => 'Condutor',
    'Apelido' => 'João'
];

$resultado = $smartecService->cadastrarCnh($dadosCnh);
```

### 5. Consultar CNH

```php
$resultado = $smartecService->consultarCnh(
    cpf: '12345678901',
    tipo: 'consulta'
);
```

### 6. Consultar Infrações

```php
$resultado = $smartecService->consultarInfracoes(
    renavam: '123456789',
    tipo: 'consulta',
    dataPesquisa: '2024-01-01'
);
```

### 7. Gerar FICI

```php
$caminhoArquivo = $smartecService->gerarFici(
    tipo: 'fici',
    ait: '123456',
    orgao: '001'
);

// O arquivo será salvo em storage/app/public/fici/YYYY/MM/filename.pdf
echo "Arquivo salvo em: " . $caminhoArquivo;
```

### 8. Solicitar Desconto de 40%

```php
$resultado = $smartecService->solicitarDescontoQuarenta(
    ait: '123456',
    codigoOrgao: '001',
    reconhecerInfracao: true,
    tipo: 'desconto'
);
```

## Tratamento de Erros

O service inclui tratamento de erros automático:

-   Logs de erro são registrados automaticamente
-   Exceções são lançadas com mensagens descritivas
-   Resposta HTTP é validada antes do retorno

### Exemplo de Tratamento

```php
try {
    $resultado = $smartecService->consultarVeiculo(...);

    // Processar resultado
    return response()->json($resultado);

} catch (\Exception $e) {
    Log::error('Erro ao consultar veículo: ' . $e->getMessage());

    return response()->json([
        'error' => 'Erro na consulta do veículo',
        'message' => $e->getMessage()
    ], 500);
}
```

## Recursos Utilizados

-   **HTTP Client**: Laravel HTTP Client (Guzzle)
-   **Storage**: Laravel Storage para arquivos PDF
-   **Logging**: Laravel Log para registro de erros
-   **Configuration**: Sistema de configuração do Laravel
-   **Type Hints**: PHP 8+ com tipagem forte

## Melhorias Implementadas

1. **Namespace correto**: `App\Services\`
2. **Configuração centralizada**: Token vem do `.env`
3. **HTTP Client moderno**: Usa Laravel HTTP em vez de cURL
4. **Tratamento de erros**: Logs automáticos e exceções estruturadas
5. **Storage organizado**: Arquivos FICI salvos com estrutura de pastas por data
6. **Type hints**: Métodos com tipagem forte para melhor IDE support
7. **Documentação**: Métodos bem documentados com PHPDoc
8. **Padrões Laravel**: Segue convenções e melhores práticas

## Migração do Código Antigo

Se você tem código usando a classe antiga `integrador_smartec`, aqui estão as equivalências:

| Método Antigo                                       | Método Novo                             |
| --------------------------------------------------- | --------------------------------------- |
| `integrador_smartec::veiculo()`                     | `$service->consultarVeiculo()`          |
| `integrador_smartec::infracoes_indicar()`           | `$service->indicarInfracao()`           |
| `integrador_smartec::excluir_indicacao()`           | `$service->excluirIndicacao()`          |
| `integrador_smartec::cadastro_cnh()`                | `$service->cadastrarCnh()`              |
| `integrador_smartec::cnh()`                         | `$service->consultarCnh()`              |
| `integrador_smartec::infracoes()`                   | `$service->consultarInfracoes()`        |
| `integrador_smartec::gerar_fici()`                  | `$service->gerarFici()`                 |
| `integrador_smartec::solicitar_desconto_quarenta()` | `$service->solicitarDescontoQuarenta()` |
