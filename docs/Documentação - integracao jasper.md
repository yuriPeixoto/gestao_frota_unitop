Classe PHP para integração com JasperReports Server via API REST, permitindo a execução de relatórios em diversos formatos (como PDF, HTML, XLS, etc).

🧩 Namespace
php
Copiar
Editar
namespace App\Traits;
🏗️ Construtor
php
Copiar
Editar
__construct(
    string $url,
    string $reportPath,
    string $format,
    string $username,
    string $password,
    array $params = []
)
Parâmetros:

Parâmetro	Tipo	Descrição
$url	string	URL base do JasperReports Server (ex: http://localhost:8080/jasperserver)
$reportPath	string	Caminho completo do relatório no servidor (ex: /reports/meu_relatorio)
$format	string	Formato do relatório (pdf, html, xls, etc.)
$username	string	Usuário para autenticação
$password	string	Senha do usuário
$params	array	(Opcional) Parâmetros a serem passados para o relatório
📦 Métodos
execute()
Executa o relatório com os parâmetros fornecidos.

Retorno:
string — Conteúdo do relatório gerado (em PDF, HTML, etc), conforme o formato escolhido.

🔒 Método Privado
getQueryString()
Constrói a string de consulta (query string) a partir dos parâmetros fornecidos.

📌 Exemplo de Uso
php
Copiar
Editar
use App\Traits\JasperServerIntegration;

$jasper = new JasperServerIntegration(
    'http://localhost:8080/jasperserver',
    '/reports/meu_relatorio',
    'pdf',
    'usuario',
    'senha',
    [
        'ID_CLIENTE' => 123,
        'DATA_INICIO' => '2024-01-01',
        'DATA_FIM' => '2024-12-31'
    ]
);

$pdfContent = $jasper->execute();

// Salvar como arquivo PDF
file_put_contents('relatorio.pdf', $pdfContent);
🛠️ Requisitos
PHP com cURL habilitado

JasperReports Server configurado e com autenticação habilitada

Relatórios publicados no JasperServer no caminho correto

⚠️ Observações
O método execute() retorna o conteúdo binário do relatório. Para formatos como PDF, use file_put_contents() para salvar.

O timeout está configurado como 90 segundos, podendo ser ajustado conforme necessá