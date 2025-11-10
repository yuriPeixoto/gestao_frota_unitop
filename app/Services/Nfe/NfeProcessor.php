<?php

namespace App\Services\Nfe;

use App\Services\Nfe\Contracts\NfeProcessorInterface;
use App\Services\Nfe\Contracts\NfePersistenceInterface;
use App\Services\Nfe\Traits\EmailSanitizerTrait;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use SimpleXMLElement;

class NfeProcessor implements NfeProcessorInterface
{
    use EmailSanitizerTrait;

    /** @var string */
    private string $caminho = '';

    /** @var NfePersistenceInterface */
    private NfePersistenceInterface $persistencia;

    /** @var SimpleXMLElement|null */
    private ?SimpleXMLElement $xml = null;

    /** @var string */
    private string $nfeNamespace = 'http://www.portalfiscal.inf.br/nfe';

    /** @var array */
    private array $registeredNamespaces = [];

    /**
     * Construtor
     *
     * @param NfePersistenceInterface $persistencia
     */
    public function __construct(NfePersistenceInterface $persistencia)
    {
        $this->persistencia = $persistencia;
    }

    /**
     * Define o caminho do arquivo XML a ser processado.
     *
     * @param string $path
     * @return self
     */
    public function setCaminho(string $path): self
    {
        $this->caminho = $path;
        return $this;
    }

    /**
     * Processa e salva os dados do XML.
     *
     * @return array
     */
    public function save(): array
    {
        try {
            $this->validarArquivo();
            $this->carregarXML();
            $this->processarXML();

            return $this->persistencia->insert();
        } catch (\Exception $e) {
            $errorMsg = "Erro ao processar XML: " . $e->getMessage();
            Log::channel('nfe')->error($errorMsg);
            Log::channel('nfe')->debug("Stack trace: " . $e->getTraceAsString());

            return [
                'success' => false,
                'error' => $errorMsg
            ];
        }
    }

    /**
     * Valida se o arquivo existe e pode ser lido.
     *
     * @throws RuntimeException
     */
    private function validarArquivo(): void
    {
        if (empty($this->caminho)) {
            throw new RuntimeException("Caminho do arquivo não definido");
        }

        if (!file_exists($this->caminho)) {
            throw new RuntimeException("Arquivo não encontrado: {$this->caminho}");
        }

        if (!is_readable($this->caminho)) {
            throw new RuntimeException("Arquivo não pode ser lido: {$this->caminho}");
        }
    }

    /**
     * Carrega o conteúdo do XML.
     *
     * @throws RuntimeException
     */
    private function carregarXML(): void
    {
        libxml_use_internal_errors(true);

        // Lê o conteúdo do arquivo
        $xmlContent = file_get_contents($this->caminho);
        if ($xmlContent === false) {
            throw new RuntimeException("Não foi possível ler o conteúdo do arquivo: {$this->caminho}");
        }

        // Carrega o XML
        $this->xml = simplexml_load_string($xmlContent);

        if ($this->xml === false) {
            $errors = libxml_get_errors();
            libxml_clear_errors();

            $errorMsg = "Falha ao carregar XML:";
            foreach ($errors as $error) {
                $errorMsg .= "\nLinha {$error->line}: {$error->message}";
            }

            throw new RuntimeException($errorMsg);
        }

        // Registra os namespaces do XML
        $this->registrarNamespaces();

        Log::channel('nfe')->debug("XML carregado com sucesso: " . basename($this->caminho));
    }

    /**
     * Registra os namespaces encontrados no XML.
     */
    private function registrarNamespaces(): void
    {
        $this->registeredNamespaces = $this->xml->getNamespaces(true);

        // Registra o namespace padrão com um prefixo 'nfe' para facilitar o acesso
        if (isset($this->registeredNamespaces[''])) {
            $this->xml->registerXPathNamespace('nfe', $this->registeredNamespaces['']);
        }

        // Registra explicitamente o namespace NFe se presente
        if (in_array($this->nfeNamespace, $this->registeredNamespaces)) {
            $this->xml->registerXPathNamespace('nfe', $this->nfeNamespace);
        }

        Log::channel('nfe')->debug("Namespaces registrados: " . json_encode($this->registeredNamespaces));
    }

    /**
     * Encontra o nó NFe no documento
     *
     * @return SimpleXMLElement|null
     */
    private function encontrarNoNFe(): ?SimpleXMLElement
    {
        // Primeiro tenta encontrar no documento diretamente
        if (isset($this->xml->NFe)) {
            return $this->xml->NFe;
        }

        // Tenta usando XPath considerando namespaces
        $nfeNodes = $this->xml->xpath('//nfe:NFe');
        if (!empty($nfeNodes)) {
            return $nfeNodes[0];
        }

        // Tenta outras alternativas de localização do nó NFe
        $alternatives = [
            '//NFe',
            '//*[local-name()="NFe"]'
        ];

        foreach ($alternatives as $xpathQuery) {
            $nfeNodes = $this->xml->xpath($xpathQuery);
            if (!empty($nfeNodes)) {
                return $nfeNodes[0];
            }
        }

        // Se chegou aqui, não encontrou o nó NFe
        Log::channel('nfe')->error("Não foi possível encontrar o nó NFe no documento");
        return null;
    }

    /**
     * Encontra o nó infNFe dentro de um nó NFe
     *
     * @param SimpleXMLElement $nfeNode
     * @return SimpleXMLElement|null
     */
    private function encontrarNoInfNFe(SimpleXMLElement $nfeNode): ?SimpleXMLElement
    {
        // Primeiro tenta acessar diretamente
        if (isset($nfeNode->infNFe)) {
            return $nfeNode->infNFe;
        }

        // Tenta usando XPath com namespace
        $infNFeNodes = $nfeNode->xpath('./nfe:infNFe');
        if (!empty($infNFeNodes)) {
            return $infNFeNodes[0];
        }

        // Tenta alternativas de localização do nó infNFe
        $alternatives = [
            './infNFe',
            './*[local-name()="infNFe"]'
        ];

        foreach ($alternatives as $xpathQuery) {
            $infNFeNodes = $nfeNode->xpath($xpathQuery);
            if (!empty($infNFeNodes)) {
                return $infNFeNodes[0];
            }
        }

        // Se chegou aqui, não encontrou o nó infNFe
        Log::channel('nfe')->error("Não foi possível encontrar o nó infNFe dentro do nó NFe");
        return null;
    }

    /**
     * Processa o conteúdo do XML carregado com tratamento adequado de namespaces.
     *
     * @throws RuntimeException
     */
    private function processarXML(): void
    {
        if (!$this->xml) {
            throw new RuntimeException("XML não foi carregado");
        }

        Log::channel('nfe')->debug("Iniciando processamento do XML");

        try {
            // Verifica se é um documento nfeProc ou NFe direto
            $nfeNode = $this->encontrarNoNFe();
            if (!$nfeNode) {
                throw new RuntimeException("Nó 'NFe' não encontrado no XML");
            }

            // Busca o nó infNFe dentro do nó NFe
            $infNFeNode = $this->encontrarNoInfNFe($nfeNode);
            if (!$infNFeNode) {
                throw new RuntimeException("Nó 'infNFe' não encontrado no XML");
            }

            // Processa o nó infNFe
            $this->processarInfNFe($infNFeNode);

            // Busca e processa os nós filhos do infNFe com tratamento de namespace
            $ideNode = $this->encontrarNoFilho($infNFeNode, 'ide');
            if (!$ideNode) {
                throw new RuntimeException("Nó 'ide' não encontrado no XML");
            }
            $this->processarIdentificacao($ideNode);

            $emitNode = $this->encontrarNoFilho($infNFeNode, 'emit');
            if (!$emitNode) {
                throw new RuntimeException("Nó 'emit' não encontrado no XML");
            }
            $this->processarEmitente($emitNode);

            $destNode = $this->encontrarNoFilho($infNFeNode, 'dest');
            if ($destNode) {
                $this->processarDestinatario($destNode);
            } else {
                Log::channel('nfe')->warning("Nó 'dest' não encontrado no XML, isso é incomum");
            }

            $detNodes = $this->encontrarNosFilhos($infNFeNode, 'det');
            if (empty($detNodes)) {
                throw new RuntimeException("Nós 'det' (itens) não encontrados no XML");
            }
            $this->processarItens($detNodes);

            // Processa nós opcionais com verificação
            $totalNode = $this->encontrarNoFilho($infNFeNode, 'total');
            if ($totalNode) {
                $icmsTotNode = $this->encontrarNoFilho($totalNode, 'ICMSTot');
                if ($icmsTotNode) {
                    $this->processarTotais($icmsTotNode);
                }
            }

            $transpNode = $this->encontrarNoFilho($infNFeNode, 'transp');
            if ($transpNode) {
                $this->processarTransporte($transpNode);
            }

            $cobrNode = $this->encontrarNoFilho($infNFeNode, 'cobr');
            if ($cobrNode) {
                $this->processarCobranca($cobrNode);
            }

            // Verifica se há informações complementares
            $infAdicNode = $this->encontrarNoFilho($infNFeNode, 'infAdic');
            if ($infAdicNode) {
                $infCplNode = $this->encontrarNoFilho($infAdicNode, 'infCpl');
                if ($infCplNode) {
                    $this->persistencia->setInfCpl((string)$infCplNode);
                }
            }

            // Processa a assinatura se existir
            $signatureNode = $this->encontrarNoFilho($nfeNode, 'Signature', 'http://www.w3.org/2000/09/xmldsig#');
            if ($signatureNode) {
                $this->processarAssinatura($signatureNode);
            }
        } catch (\Exception $e) {
            Log::channel('nfe')->error("Erro ao processar estrutura XML: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Encontra um nó filho pelo nome considerando namespaces
     *
     * @param SimpleXMLElement $parentNode
     * @param string $childName
     * @param string|null $namespace
     * @return SimpleXMLElement|null
     */
    private function encontrarNoFilho(
        SimpleXMLElement $parentNode,
        string $childName,
        ?string $namespace = null
    ): ?SimpleXMLElement {
        // Tenta acessar diretamente primeiro
        if (isset($parentNode->$childName)) {
            return $parentNode->$childName;
        }

        // Se foi especificado um namespace, tenta com ele
        if ($namespace) {
            $nodes = $parentNode->children($namespace)->$childName;
            if (count($nodes) > 0) {
                return $nodes;
            }
        }

        // Tenta com o namespace padrão da NFe
        $nodes = $parentNode->children($this->nfeNamespace)->$childName;
        if (count($nodes) > 0) {
            return $nodes;
        }

        // Tenta usando XPath para qualquer namespace
        $query = "./*[local-name()='$childName']";
        $nodes = $parentNode->xpath($query);
        if (!empty($nodes)) {
            return $nodes[0];
        }

        return null;
    }

    /**
     * Encontra múltiplos nós filhos pelo nome considerando namespaces
     *
     * @param SimpleXMLElement $parentNode
     * @param string $childName
     * @param string|null $namespace
     * @return SimpleXMLElement[]
     */
    private function encontrarNosFilhos(SimpleXMLElement $parentNode, string $childName, ?string $namespace = null): array
    {
        // Primeiro tenta com acesso direto
        $directChildren = $parentNode->$childName;
        if (count($directChildren) > 0) {
            // Convertemos para array para garantir o tipo correto de retorno
            $result = [];
            foreach ($directChildren as $child) {
                $result[] = $child;
            }
            return $result;
        }

        // Se foi especificado um namespace, tenta com ele
        if ($namespace) {
            $nsChildren = $parentNode->children($namespace)->$childName;
            if (count($nsChildren) > 0) {
                $result = [];
                foreach ($nsChildren as $child) {
                    $result[] = $child;
                }
                return $result;
            }
        }

        // Tenta com o namespace padrão da NFe
        $nfeChildren = $parentNode->children($this->nfeNamespace)->$childName;
        if (count($nfeChildren) > 0) {
            $result = [];
            foreach ($nfeChildren as $child) {
                $result[] = $child;
            }
            return $result;
        }

        // Tenta usando XPath para qualquer namespace
        $query = "./*[local-name()='$childName']";
        $nodes = $parentNode->xpath($query);
        if (!empty($nodes)) {
            return $nodes;
        }

        return [];
    }

    /**
     * Processa informações básicas da NF-e
     */
    private function processarInfNFe(SimpleXMLElement $xmlo): void
    {
        // Verifica se o atributo Id existe
        $id = null;

        // Tenta obter o atributo Id diretamente
        if (isset($xmlo['Id'])) {
            $id = (string)$xmlo['Id'];
        } else {
            $attributes = $xmlo->attributes();
            foreach ($attributes as $key => $value) {
                if (strtolower($key) === 'id') {
                    $id = (string)$value;
                    break;
                }
            }
        }

        if (empty($id)) {
            throw new RuntimeException("Atributo 'Id' não encontrado ou vazio no nó infNFe");
        }

        // Remove o prefixo 'NFe' do ID se existir
        if (strpos($id, 'NFe') === 0) {
            $id = substr($id, 3);
        }

        $this->persistencia->setInfNFe($id);
        Log::channel('nfe')->debug("Processou infNFe com ID: $id");
    }

    /**
     * Processa identificação da NF-e com verificações de segurança
     */
    private function processarIdentificacao(SimpleXMLElement $ide): void
    {
        // IMPORTANTE: Armazenar dhEmi para uso posterior se dhSaiEnt estiver vazio
        $dhEmiValue = null;
        $dhSaiEntValue = null;

        // Primeiro, captura o valor de dhEmi para uso posterior
        if (isset($ide->dhEmi) && (string)$ide->dhEmi !== '') {
            $dhEmiValue = (string)$ide->dhEmi;
        } else {
            // Tenta com namespace
            $children = $ide->children($this->nfeNamespace);
            if (isset($children->dhEmi) && (string)$children->dhEmi !== '') {
                $dhEmiValue = (string)$children->dhEmi;
            }
        }

        // Verifica se dhSaiEnt existe e tem valor válido
        if (isset($ide->dhSaiEnt) && (string)$ide->dhSaiEnt !== '') {
            $dhSaiEntValue = (string)$ide->dhSaiEnt;
        } else {
            // Tenta com namespace
            $children = $ide->children($this->nfeNamespace);
            if (isset($children->dhSaiEnt) && (string)$children->dhSaiEnt !== '') {
                $dhSaiEntValue = (string)$children->dhSaiEnt;
            }
        }

        // Campos obrigatórios com seus tipos e validações específicas
        $camposObrigatorios = [
            'natOp'    => [
                'metodo' => 'setNatOp',
                'mensagemErro' => 'Natureza da operação não encontrada'
            ],
            'serie'    => [
                'metodo' => 'setSerie',
                'tipo' => 'int',
                'mensagemErro' => 'Série não encontrada'
            ],
            'nNF'      => [
                'metodo' => 'setNNF',
                'tipo' => 'int',
                'mensagemErro' => 'Número da NF não encontrado'
            ],
            'dhEmi'    => [
                'metodo' => 'setDhEmi',
                'mensagemErro' => 'Data de emissão não encontrada'
            ],
            'cUF'      => [
                'metodo' => 'setCUF',
                'tipo' => 'int',
                'mensagemErro' => 'Código UF não encontrado'
            ],
            'cNF'      => [
                'metodo' => 'setCNF',
                'tipo' => 'int',
                'mensagemErro' => 'Código numérico da NF não encontrado'
            ],
            'mod'      => [
                'metodo' => 'setMod',
                'tipo' => 'int',
                'mensagemErro' => 'Modelo do documento fiscal não encontrado'
            ],
            'tpNF'     => [
                'metodo' => 'setTpNF',
                'tipo' => 'int',
                'mensagemErro' => 'Tipo de operação não encontrado'
            ]
        ];

        // Campos opcionais com validação (removendo dhSaiEnt que será tratado separadamente)
        $camposOpcionais = [
            'indPag'   => ['metodo' => 'setIndPag', 'tipo' => 'int', 'opcional' => true],
            // dhSaiEnt será tratado separadamente abaixo
            'idDest'   => ['metodo' => 'setIdDest', 'tipo' => 'int', 'opcional' => true],
            'cMunFG'   => ['metodo' => 'setCMunFG', 'tipo' => 'int', 'opcional' => true],
            'tpImp'    => ['metodo' => 'setTpImp', 'tipo' => 'int', 'opcional' => true],
            'tpEmis'   => ['metodo' => 'setTpEmis', 'tipo' => 'int', 'opcional' => true],
            'cDV'      => ['metodo' => 'setCDV', 'tipo' => 'int', 'opcional' => true],
            'tpAmb'    => ['metodo' => 'setTpAmb', 'tipo' => 'int', 'opcional' => true],
            'finNFe'   => ['metodo' => 'setFinNFe', 'tipo' => 'int', 'opcional' => true],
            'indFinal' => ['metodo' => 'setIndFinal', 'tipo' => 'int', 'opcional' => true],
            'indPres'  => ['metodo' => 'setIndPres', 'tipo' => 'int', 'opcional' => true],
            'procEmi'  => ['metodo' => 'setProcEmi', 'tipo' => 'int', 'opcional' => true],
            'verProc'  => ['metodo' => 'setVerProc', 'opcional' => true]
        ];

        // Processa campos obrigatórios com mensagens de erro específicas
        $this->processarCamposComValidacao($ide, $camposObrigatorios, false);

        // TRATAMENTO ESPECIAL PARA dhSaiEnt
        // Se dhSaiEnt está vazio, é hífen ou inválido, usa dhEmi
        if (empty($dhSaiEntValue) || trim($dhSaiEntValue) === '' || $dhSaiEntValue === '-') {
            if (!empty($dhEmiValue)) {
                Log::channel('nfe')->info("Campo dhSaiEnt vazio ou inválido, copiando valor de dhEmi: {$dhEmiValue}");
                $this->persistencia->setDhSaiEnt($dhEmiValue);
            } else {
                // Se ambos estão vazios, usa a data atual como fallback
                $fallbackDate = now()->format('Y-m-d\TH:i:sP');
                Log::channel('nfe')->warning("Campos dhEmi e dhSaiEnt vazios, usando data atual: {$fallbackDate}");
                $this->persistencia->setDhSaiEnt($fallbackDate);
            }
        } else {
            // dhSaiEnt tem valor válido, usa normalmente
            $this->persistencia->setDhSaiEnt($dhSaiEntValue);
        }

        // Processa campos opcionais (exceto dhSaiEnt que já foi tratado)
        $this->processarCampos($ide, $camposOpcionais, true);
    }

    /**
     * Processa campos com mensagens de erro específicas
     */
    private function processarCamposComValidacao(SimpleXMLElement $elemento, array $campos, bool $camposOpcionais): void
    {
        foreach ($campos as $campo => $config) {
            // Tenta obter o valor diretamente
            $valor = null;
            $encontrado = false;

            if (isset($elemento->$campo) && (string)$elemento->$campo !== '') {
                $valor = $elemento->$campo;
                $encontrado = true;
            } else {
                $children = $elemento->children($this->nfeNamespace);
                if (isset($children->$campo) && (string)$children->$campo !== '') {
                    $valor = $children->$campo;
                    $encontrado = true;
                }
            }

            // Se encontrou o campo, processa o valor
            if ($encontrado) {
                $valorConvertido = $this->converterValor($valor, $config['tipo'] ?? 'string');

                // CORREÇÃO: Removida a opção de adicionar aspas ao valor
                // Antes: if (isset($config['quote']) && $config['quote']) { $valorConvertido = "'$valorConvertido'"; }
                // Agora: sempre passa o valor sem aspas adicionais

                $this->persistencia->{$config['metodo']}($valorConvertido);
            } elseif (!$camposOpcionais) {
                $mensagem = $config['mensagemErro'] ?? "Campo obrigatório não encontrado: $campo";
                Log::channel('nfe')->error($mensagem);
                throw new RuntimeException($mensagem);
            }
        }
    }

    /**
     * Processa dados do emitente
     */
    private function processarEmitente(SimpleXMLElement $emit): void
    {
        // Campos obrigatórios do emitente com validações
        $camposObrigatorios = [
            'CNPJ'  => [
                'metodo' => 'setCnpjNfeEmissor',
                'mensagemErro' => 'CNPJ do emitente não encontrado'
            ],
            'IE'    => [
                'metodo' => 'setIeNfeEmissor',
                'tipo' => 'int',
                'mensagemErro' => 'Inscrição Estadual do emitente não encontrada'
            ],
            'CRT'   => [
                'metodo' => 'setCrtNfeEmissor',
                'tipo' => 'int',
                'mensagemErro' => 'Código de Regime Tributário não encontrado'
            ],
            'xNome' => [
                'metodo' => 'setxNomeNfeEmissor',
                'mensagemErro' => 'Nome do emitente não encontrado'
            ]
        ];

        // Campos opcionais do emitente
        $camposOpcionais = [
            'xFant' => ['metodo' => 'setxFantNfeEmissor', 'opcional' => true]
        ];

        $this->processarCamposComValidacao($emit, $camposObrigatorios, false);
        $this->processarCampos($emit, $camposOpcionais, true);

        // Processa o endereço do emitente se existir
        $enderEmitNode = $this->encontrarNoFilho($emit, 'enderEmit');
        if ($enderEmitNode) {
            $this->processarEnderecoEmitente($enderEmitNode);
        } else {
            Log::channel('nfe')->warning("Endereço do emitente não encontrado");
        }
    }

    /**
     * Processa endereço do emitente
     */
    private function processarEnderecoEmitente(SimpleXMLElement $enderEmit): void
    {
        // Campos obrigatórios do endereço
        $camposObrigatorios = [
            'xLgr'    => [
                'metodo' => 'setxLgrNfeEmissor',
                'mensagemErro' => 'Logradouro do emitente não encontrado'
            ],
            'nro'     => [
                'metodo' => 'setNroNfeEmissor',
                'tipo' => 'int',
                'mensagemErro' => 'Número do endereço do emitente não encontrado'
            ],
            'xBairro' => [
                'metodo' => 'setxBairroNfeEmissor',
                'mensagemErro' => 'Bairro do emitente não encontrado'
            ],
            'cMun'    => [
                'metodo' => 'setcMunNfeEmissor',
                'tipo' => 'int',
                'mensagemErro' => 'Código do município do emitente não encontrado'
            ],
            'xMun'    => [
                'metodo' => 'setxMunNfeEmissor',
                'mensagemErro' => 'Município do emitente não encontrado'
            ],
            'UF'      => [
                'metodo' => 'setUfNfeEmissor',
                'mensagemErro' => 'UF do emitente não encontrada'
            ],
            'CEP'     => [
                'metodo' => 'setCepNfeEmissor',
                'tipo' => 'int',
                'mensagemErro' => 'CEP do emitente não encontrado'
            ]
        ];

        // Campos opcionais do endereço
        $camposOpcionais = [
            'fone'  => ['metodo' => 'setFoneNfeEmissor', 'opcional' => true],
            'cPais' => ['metodo' => 'setcPaisNfeEmissor', 'tipo' => 'int', 'opcional' => true],
            'xPais' => ['metodo' => 'setxPaisNfeEmissor', 'opcional' => true]
        ];

        $this->processarCamposComValidacao($enderEmit, $camposObrigatorios, false);
        $this->processarCampos($enderEmit, $camposOpcionais, true);
    }

    /**
     * Processa dados do destinatário
     */
    private function processarDestinatario(SimpleXMLElement $dest): void
    {
        // Verifica se tem CNPJ ou CPF
        $temIdentificacao = false;

        if (isset($dest->CNPJ) && (string)$dest->CNPJ !== '') {
            $this->persistencia->setCnpjNfeDestinatario((string)$dest->CNPJ);
            $temIdentificacao = true;
        } elseif (isset($dest->CPF) && (string)$dest->CPF !== '') {
            $this->persistencia->setCpfNfeDestinatario((string)$dest->CPF);
            $temIdentificacao = true;
        }

        if (!$temIdentificacao) {
            Log::channel('nfe')->warning("CNPJ ou CPF do destinatário não encontrado");
        }

        // Trata o email com sanitização
        if (isset($dest->email) && (string)$dest->email !== '') {
            $emailSanitizado = $this->sanitizeEmails((string)$dest->email);
            if ($emailSanitizado !== null) {
                $this->persistencia->setEmailNfeDestinatario($emailSanitizado);
            } else {
                Log::channel('nfe')->warning('Email inválido encontrado na NFe', [
                    'email_original' => (string)$dest->email
                ]);
                // Define email como string vazia para evitar erros
                $this->persistencia->setEmailNfeDestinatario('');
            }
        }

        // Campos obrigatórios
        $camposObrigatorios = [
            'xNome'     => [
                'metodo' => 'setXNomeNfeDestinatario',
                'mensagemErro' => 'Nome do destinatário não encontrado'
            ]
        ];

        // O campo indIEDest pode não estar presente em NFes mais antigas
        if (isset($dest->indIEDest) && (string)$dest->indIEDest !== '') {
            $this->persistencia->setIndIEDestNfeDestinatario((string)$dest->indIEDest);
        } else {
            Log::channel('nfe')->warning("Campo 'indIEDest' não encontrado, pode ser uma NFe antiga");
            // Define um valor padrão para evitar erros
            $this->persistencia->setIndIEDestNfeDestinatario('9'); // 9 = Não contribuinte
        }

        $this->processarCamposComValidacao($dest, $camposObrigatorios, false);

        // Processa endereço do destinatário se existir
        $enderDestNode = $this->encontrarNoFilho($dest, 'enderDest');
        if ($enderDestNode) {
            $this->processarEnderecoDestinatario($enderDestNode);
        } else {
            Log::channel('nfe')->warning("Endereço do destinatário não encontrado");
        }
    }

    /**
     * Processa endereço do destinatário
     */
    private function processarEnderecoDestinatario(SimpleXMLElement $enderDest): void
    {
        // Campos obrigatórios do endereço
        $camposEnderecoObrig = [
            'xLgr'    => ['metodo' => 'setXLgrNfeDestinatario'],
            'nro'     => ['metodo' => 'setNroNfeDestinatario', 'tipo' => 'int'],
            'xBairro' => ['metodo' => 'setXBairroNfeDestinatario'],
            'xMun'    => ['metodo' => 'setXMunNfeDestinatario'],
            'cMun'    => ['metodo' => 'setCMunNfeDestinatario', 'tipo' => 'int'],
            'UF'      => ['metodo' => 'setUfNfeDestinatario'],
            'CEP'     => ['metodo' => 'setCepNfeDestinatario', 'tipo' => 'int']
        ];

        // Campos opcionais do endereço
        $camposEnderecoOpc = [
            'cPais' => ['metodo' => 'setCPaisNfeDestinatario', 'tipo' => 'int', 'opcional' => true],
            'xPais' => ['metodo' => 'setXPaisNfeDestinatario', 'opcional' => true],
            'fone'  => ['metodo' => 'setFoneNfeDestinatario', 'opcional' => true]
        ];

        // Processamento mais tolerante para os campos do endereço
        foreach ($camposEnderecoObrig as $campo => $config) {
            $valor = null;
            $encontrado = false;

            // Tenta várias maneiras de obter o valor do campo
            if (isset($enderDest->$campo) && (string)$enderDest->$campo !== '') {
                $valor = $enderDest->$campo;
                $encontrado = true;
            } else {
                // Tenta com namespace
                $children = $enderDest->children($this->nfeNamespace);
                if (isset($children->$campo) && (string)$children->$campo !== '') {
                    $valor = $children->$campo;
                    $encontrado = true;
                } else {
                    $nodes = $enderDest->xpath("./*[local-name()='$campo']");
                    if (!empty($nodes)) {
                        $valor = $nodes[0];
                        $encontrado = true;
                    }
                }
            }

            if ($encontrado) {
                try {
                    $valorConvertido = $this->converterValor($valor, $config['tipo'] ?? 'string');
                    $this->persistencia->{$config['metodo']}($valorConvertido);
                } catch (\Exception $e) {
                    Log::channel('nfe')
                        ->warning("Erro ao processar campo '$campo' do endereço do destinatário: " . $e->getMessage());
                }
            } else {
                Log::channel('nfe')->warning("Campo '$campo' não encontrado no endereço do destinatário");

                // Valores padrão para evitar erros
                if ($campo === 'nro') {
                    $this->persistencia->setNroNfeDestinatario(0);
                } elseif ($campo === 'cMun') {
                    $this->persistencia->setCMunNfeDestinatario(0);
                } elseif ($campo === 'CEP') {
                    $this->persistencia->setCepNfeDestinatario(0);
                } else {
                    // Para campos de texto, usa valor em branco
                    $this->persistencia->{$config['metodo']}('');
                }
            }
        }

        // Processa campos opcionais
        $this->processarCampos($enderDest, $camposEnderecoOpc, true);
    }

    /**
     * Processa itens da nota com tratamento de erros
     */
    private function processarItens($itens): void
    {
        if (count($itens) === 0) {
            throw new RuntimeException("Nenhum item encontrado na NFe");
        }

        foreach ($itens as $item) {
            // Verifica se o atributo nItem existe
            $nItem = null;

            // Tenta obter o atributo nItem diretamente
            if (isset($item['nItem'])) {
                $nItem = (int)$item['nItem'];
            } else {
                $attributes = $item->attributes();
                foreach ($attributes as $key => $value) {
                    if (strtolower($key) === 'nitem') {
                        $nItem = (int)$value;
                        break;
                    }
                }
            }

            if ($nItem === null) {
                Log::channel('nfe')
                    ->warning("Atributo 'nItem' não encontrado em um dos itens, usando índice sequencial");
                static $sequentialIndex = 1;
                $nItem = $sequentialIndex++;
            }

            $this->persistencia->setnItemProduto($nItem);

            $prodNode = $this->encontrarNoFilho($item, 'prod');
            if (!$prodNode) {
                Log::channel('nfe')->warning("Nó 'prod' não encontrado para o item $nItem");
                continue; // Pula este item
            }

            try {
                $this->processarProduto($prodNode, $nItem);
            } catch (\Exception $e) {
                Log::channel('nfe')->error("Erro ao processar produto $nItem: " . $e->getMessage());
                continue; // Tenta continuar com os próximos itens
            }

            $impostoNode = $this->encontrarNoFilho($item, 'imposto');
            if ($impostoNode) {
                $this->processarImposto($impostoNode, $nItem);
            }
        }
    }

    /**
     * Processa dados do produto com melhor tratamento de erros
     */
    private function processarProduto(SimpleXMLElement $prod, int $nItem): void
    {
        // Campos obrigatórios do produto com verificações
        $campos = [
            'cProd'     => ['metodo' => 'setcProdProduto'],
            'xProd'     => ['metodo' => 'setxProdProduto'],
            'NCM'       => [
                'metodo' => 'setNCMProduto',
                'tipo' => 'int',
                'msgErro' => "NCM não encontrado para o item $nItem"
            ],
            'CFOP'      => [
                'metodo' => 'setCFOPProduto',
                'tipo' => 'int',
                'msgErro' => "CFOP não encontrado para o item $nItem"
            ],
            'uCom'      => ['metodo' => 'setuComProduto'],
            'qCom'      => ['metodo' => 'setqComProduto', 'tipo' => 'float'],
            'vUnCom'    => ['metodo' => 'setvUnComProduto', 'tipo' => 'float'],
            'vProd'     => ['metodo' => 'setvProdProduto', 'tipo' => 'float']
        ];

        // Campos opcionais do produto
        $camposOpcionais = [
            'vDesc'    => ['metodo' => 'setvDescProduto', 'tipo' => 'float', 'opcional' => true],
            'cEAN'     => ['metodo' => 'setcEANProduto', 'opcional' => true],
            'cEANTrib' => ['metodo' => 'setcEANTribProduto', 'opcional' => true],
            'uTrib'    => ['metodo' => 'setuTribProduto', 'opcional' => true],
            'qTrib'    => ['metodo' => 'setqTribProduto', 'tipo' => 'float', 'opcional' => true],
            'vUnTrib'  => ['metodo' => 'setvUnTribProduto', 'tipo' => 'float', 'opcional' => true],
            'indTot'   => ['metodo' => 'setindTotProduto', 'tipo' => 'int', 'opcional' => true]
        ];

        // Processamento de campos obrigatórios com tratamento de erros aprimorado
        foreach ($campos as $campo => $config) {
            $valor = null;
            $encontrado = false;

            // Tenta várias maneiras de obter o valor do campo
            if (isset($prod->$campo) && (string)$prod->$campo !== '') {
                $valor = $prod->$campo;
                $encontrado = true;
            } else {
                // Tenta com namespace
                $children = $prod->children($this->nfeNamespace);
                if (isset($children->$campo) && (string)$children->$campo !== '') {
                    $valor = $children->$campo;
                    $encontrado = true;
                } else {
                    $nodes = $prod->xpath("./*[local-name()='$campo']");
                    if (!empty($nodes)) {
                        $valor = $nodes[0];
                        $encontrado = true;
                    }
                }
            }

            if ($encontrado) {
                try {
                    $valorConvertido = $this->converterValor($valor, $config['tipo'] ?? 'string');
                    $this->persistencia->{$config['metodo']}($nItem, $valorConvertido);
                } catch (\Exception $e) {
                    $msgErro = $config['msgErro'] ?? "Erro ao processar campo '$campo' do item $nItem";
                    Log::channel('nfe')->error($msgErro . ": " . $e->getMessage());
                    throw new RuntimeException($msgErro);
                }
            } else {
                $msgErro = $config['msgErro'] ?? "Campo obrigatório '$campo' não encontrado para o item $nItem";
                Log::channel('nfe')->error($msgErro);
                throw new RuntimeException($msgErro);
            }
        }

        // Processamento de campos opcionais
        foreach ($camposOpcionais as $campo => $config) {
            $valor = null;
            $encontrado = false;

            // Tenta várias maneiras de obter o valor do campo
            if (isset($prod->$campo) && (string)$prod->$campo !== '') {
                $valor = $prod->$campo;
                $encontrado = true;
            } else {
                // Tenta com namespace
                $children = $prod->children($this->nfeNamespace);
                if (isset($children->$campo) && (string)$children->$campo !== '') {
                    $valor = $children->$campo;
                    $encontrado = true;
                } else {
                    $nodes = $prod->xpath("./*[local-name()='$campo']");
                    if (!empty($nodes)) {
                        $valor = $nodes[0];
                        $encontrado = true;
                    }
                }
            }

            if ($encontrado) {
                try {
                    $valorConvertido = $this->converterValor($valor, $config['tipo'] ?? 'string');
                    $this->persistencia->{$config['metodo']}($nItem, $valorConvertido);
                } catch (\Exception $e) {
                    Log::channel('nfe')
                        ->warning("Erro ao processar campo opcional '$campo' do item $nItem: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Processa dados dos impostos com melhor tratamento de erros
     */
    private function processarImposto(SimpleXMLElement $imposto, int $nItem): void
    {
        // Trata valor total de tributos se existir
        $vTotTribNode = $this->encontrarNoFilho($imposto, 'vTotTrib');
        if ($vTotTribNode) {
            try {
                $this->persistencia->setvTotTribProduto($nItem, (float)$vTotTribNode);
            } catch (\Exception $e) {
                Log::channel('nfe')->warning("Erro ao processar vTotTrib do item $nItem: " . $e->getMessage());
            }
        }

        // Processa grupos de impostos específicos
        $icmsNode = $this->encontrarNoFilho($imposto, 'ICMS');
        if ($icmsNode) {
            $this->processarICMS($icmsNode, $nItem);
        }

        $ipiNode = $this->encontrarNoFilho($imposto, 'IPI');
        if ($ipiNode) {
            $this->processarIPI($ipiNode, $nItem);
        }

        $pisNode = $this->encontrarNoFilho($imposto, 'PIS');
        if ($pisNode) {
            $this->processarPIS($pisNode, $nItem);
        }

        $cofinsNode = $this->encontrarNoFilho($imposto, 'COFINS');
        if ($cofinsNode) {
            $this->processarCOFINS($cofinsNode, $nItem);
        }
    }

    /**
     * Processa dados do ICMS com melhor tratamento de erros
     */
    private function processarICMS(SimpleXMLElement $icms, int $nItem): void
    {
        // Verifica qual tipo de tributação ICMS está presente
        // ICMS para Simples Nacional
        $icmssn101 = $this->encontrarNoFilho($icms, 'ICMSSN101');
        if ($icmssn101) {
            $this->processarICMSSN($icmssn101, $nItem, '101');
            return;
        }

        $icmssn102 = $this->encontrarNoFilho($icms, 'ICMSSN102');
        if ($icmssn102) {
            $this->processarICMSSN($icmssn102, $nItem, '102');
            return;
        }

        $icmssn201 = $this->encontrarNoFilho($icms, 'ICMSSN201');
        if ($icmssn201) {
            $this->processarICMSSN($icmssn201, $nItem, '201');
            return;
        }

        $icmssn202 = $this->encontrarNoFilho($icms, 'ICMSSN202');
        if ($icmssn202) {
            $this->processarICMSSN($icmssn202, $nItem, '202');
            return;
        }

        $icmssn500 = $this->encontrarNoFilho($icms, 'ICMSSN500');
        if ($icmssn500) {
            $this->processarICMSSN($icmssn500, $nItem, '500');
            return;
        }

        $icmssn900 = $this->encontrarNoFilho($icms, 'ICMSSN900');
        if ($icmssn900) {
            $this->processarICMSSN($icmssn900, $nItem, '900');
            return;
        }

        // ICMS normal (não Simples Nacional)
        $icms00 = $this->encontrarNoFilho($icms, 'ICMS00');
        if ($icms00) {
            $this->processarICMSNormal($icms00, $nItem, '00');
            return;
        }

        $icms10 = $this->encontrarNoFilho($icms, 'ICMS10');
        if ($icms10) {
            $this->processarICMSNormal($icms10, $nItem, '10');
            return;
        }

        $icms20 = $this->encontrarNoFilho($icms, 'ICMS20');
        if ($icms20) {
            $this->processarICMSNormal($icms20, $nItem, '20');
            return;
        }

        $icms30 = $this->encontrarNoFilho($icms, 'ICMS30');
        if ($icms30) {
            $this->processarICMSNormal($icms30, $nItem, '30');
            return;
        }

        $icms40 = $this->encontrarNoFilho($icms, 'ICMS40');
        if ($icms40) {
            $this->processarICMSNormal($icms40, $nItem, '40');
            return;
        }

        $icms51 = $this->encontrarNoFilho($icms, 'ICMS51');
        if ($icms51) {
            $this->processarICMSNormal($icms51, $nItem, '51');
            return;
        }

        $icms60 = $this->encontrarNoFilho($icms, 'ICMS60');
        if ($icms60) {
            $this->processarICMSNormal($icms60, $nItem, '60');
            return;
        }

        $icms70 = $this->encontrarNoFilho($icms, 'ICMS70');
        if ($icms70) {
            $this->processarICMSNormal($icms70, $nItem, '70');
            return;
        }

        $icms90 = $this->encontrarNoFilho($icms, 'ICMS90');
        if ($icms90) {
            $this->processarICMSNormal($icms90, $nItem, '90');
            return;
        }

        // Se chegou aqui, não encontrou nenhum grupo ICMS
        Log::channel('nfe')->warning("Grupo ICMS não identificado para o item $nItem");
    }

    /**
     * Processa ICMS para Simples Nacional
     */
    private function processarICMSSN(SimpleXMLElement $icmssn, int $nItem, string $tipo): void
    {
        try {
            $origNode = $this->encontrarNoFilho($icmssn, 'orig');
            if ($origNode) {
                $this->persistencia->setorigProduto($nItem, (int)$origNode);
            }

            $csosnNode = $this->encontrarNoFilho($icmssn, 'CSOSN');
            if ($csosnNode) {
                $this->persistencia->setCSOSNProduto($nItem, (int)$csosnNode);
            }

            // Campos específicos para certos tipos
            if (in_array($tipo, ['101', '201', '900'])) {
                $pCredSNNode = $this->encontrarNoFilho($icmssn, 'pCredSN');
                if ($pCredSNNode) {
                    $this->persistencia->setpCredSNProduto($nItem, (float)$pCredSNNode);
                }

                $vCredICMSSNNode = $this->encontrarNoFilho($icmssn, 'vCredICMSSN');
                if ($vCredICMSSNNode) {
                    $this->persistencia->setvCredICMSSNProduto($nItem, (float)$vCredICMSSNNode);
                }
            }
        } catch (\Exception $e) {
            Log::channel('nfe')->warning("Erro ao processar ICMS SN $tipo para o item $nItem: " . $e->getMessage());
        }
    }

    /**
     * Processa ICMS normal (não Simples Nacional)
     */
    private function processarICMSNormal(SimpleXMLElement $icms, int $nItem, string $cst): void
    {
        try {
            $origNode = $this->encontrarNoFilho($icms, 'orig');
            if ($origNode) {
                $this->persistencia->setorigProduto($nItem, (int)$origNode);
            }

            $cstNode = $this->encontrarNoFilho($icms, 'CST');
            if ($cstNode) {
                $this->persistencia->setCST1Produto($nItem, (int)$cstNode);
            }

            // Outros campos específicos podem ser processados aqui conforme necessidade
        } catch (\Exception $e) {
            Log::channel('nfe')->warning("Erro ao processar ICMS $cst para o item $nItem: " . $e->getMessage());
        }
    }

    /**
     * Processa dados do IPI
     */
    private function processarIPI(SimpleXMLElement $ipi, int $nItem): void
    {
        try {
            $clEnqNode = $this->encontrarNoFilho($ipi, 'clEnq');
            if ($clEnqNode) {
                $this->persistencia->setclEnqProduto($nItem, (int)$clEnqNode);
            }

            $cnpjProdNode = $this->encontrarNoFilho($ipi, 'CNPJProd');
            if ($cnpjProdNode) {
                $this->persistencia->setCNPJProdProduto($nItem, (int)$cnpjProdNode);
            }

            $cEnqNode = $this->encontrarNoFilho($ipi, 'cEnq');
            if ($cEnqNode) {
                $this->persistencia->setcEnqProduto($nItem, (int)$cEnqNode);
            }

            // Processa CST do IPI - verifica qual grupo está presente
            $ipintNode = $this->encontrarNoFilho($ipi, 'IPINT');
            if ($ipintNode) {
                $cstNode = $this->encontrarNoFilho($ipintNode, 'CST');
                if ($cstNode) {
                    $this->persistencia->setCST1Produto($nItem, (int)$cstNode);
                }
            } else {
                $ipiTribNode = $this->encontrarNoFilho($ipi, 'IPITrib');
                if ($ipiTribNode) {
                    $cstNode = $this->encontrarNoFilho($ipiTribNode, 'CST');
                    if ($cstNode) {
                        $this->persistencia->setCST1Produto($nItem, (int)$cstNode);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::channel('nfe')->warning("Erro ao processar IPI para o item $nItem: " . $e->getMessage());
        }
    }

    /**
     * Processa dados do PIS
     */
    private function processarPIS(SimpleXMLElement $pis, int $nItem): void
    {
        try {
            // Verifica qual grupo de PIS está presente
            $pisntNode = $this->encontrarNoFilho($pis, 'PISNT');
            if ($pisntNode) {
                $cstNode = $this->encontrarNoFilho($pisntNode, 'CST');
                if ($cstNode) {
                    $this->persistencia->setCST2Produto($nItem, (int)$cstNode);
                }
                return;
            }

            $pisAliqNode = $this->encontrarNoFilho($pis, 'PISAliq');
            if ($pisAliqNode) {
                $cstNode = $this->encontrarNoFilho($pisAliqNode, 'CST');
                if ($cstNode) {
                    $this->persistencia->setCST2Produto($nItem, (int)$cstNode);
                }
                return;
            }

            $pisQtdeNode = $this->encontrarNoFilho($pis, 'PISQtde');
            if ($pisQtdeNode) {
                $cstNode = $this->encontrarNoFilho($pisQtdeNode, 'CST');
                if ($cstNode) {
                    $this->persistencia->setCST2Produto($nItem, (int)$cstNode);
                }
                return;
            }

            $pisOutrNode = $this->encontrarNoFilho($pis, 'PISOutr');
            if ($pisOutrNode) {
                $cstNode = $this->encontrarNoFilho($pisOutrNode, 'CST');
                if ($cstNode) {
                    $this->persistencia->setCST2Produto($nItem, (int)$cstNode);
                }
                return;
            }
        } catch (\Exception $e) {
            Log::channel('nfe')->warning("Erro ao processar PIS para o item $nItem: " . $e->getMessage());
        }
    }

    /**
     * Processa dados do COFINS
     */
    private function processarCOFINS(SimpleXMLElement $cofins, int $nItem): void
    {
        try {
            // Verifica qual grupo de COFINS está presente
            $cofinsntNode = $this->encontrarNoFilho($cofins, 'COFINSNT');
            if ($cofinsntNode) {
                $cstNode = $this->encontrarNoFilho($cofinsntNode, 'CST');
                if ($cstNode) {
                    $this->persistencia->setCST3Produto($nItem, (int)$cstNode);
                }
                return;
            }

            $cofinsAliqNode = $this->encontrarNoFilho($cofins, 'COFINSAliq');
            if ($cofinsAliqNode) {
                $cstNode = $this->encontrarNoFilho($cofinsAliqNode, 'CST');
                if ($cstNode) {
                    $this->persistencia->setCST3Produto($nItem, (int)$cstNode);
                }
                return;
            }

            $cofinsQtdeNode = $this->encontrarNoFilho($cofins, 'COFINSQtde');
            if ($cofinsQtdeNode) {
                $cstNode = $this->encontrarNoFilho($cofinsQtdeNode, 'CST');
                if ($cstNode) {
                    $this->persistencia->setCST3Produto($nItem, (int)$cstNode);
                }
                return;
            }

            $cofinsOutrNode = $this->encontrarNoFilho($cofins, 'COFINSOutr');
            if ($cofinsOutrNode) {
                $cstNode = $this->encontrarNoFilho($cofinsOutrNode, 'CST');
                if ($cstNode) {
                    $this->persistencia->setCST3Produto($nItem, (int)$cstNode);
                }
                return;
            }
        } catch (\Exception $e) {
            Log::channel('nfe')->warning("Erro ao processar COFINS para o item $nItem: " . $e->getMessage());
        }
    }

    /**
     * Processa totais da nota
     */
    private function processarTotais(SimpleXMLElement $total): void
    {
        // Campos obrigatórios
        $camposObrigatorios = [
            'vNF'    => ['metodo' => 'setVNF', 'tipo' => 'float']
        ];

        // Campos opcionais
        $camposOpcionais = [
            'vFrete' => ['metodo' => 'setVFrete', 'tipo' => 'float', 'opcional' => true],
            'vDesc'  => ['metodo' => 'setVDesc', 'tipo' => 'float', 'opcional' => true],
            'vBC'    => ['metodo' => 'setVBC', 'tipo' => 'float', 'opcional' => true],
            'vICMS'  => ['metodo' => 'setVICMS', 'tipo' => 'float', 'opcional' => true],
            'vICMSDeson' => ['metodo' => 'setVICMSDeson', 'tipo' => 'float', 'opcional' => true],
            'vBCST'  => ['metodo' => 'setVBCST', 'tipo' => 'float', 'opcional' => true],
            'vST'    => ['metodo' => 'setVST', 'tipo' => 'float', 'opcional' => true],
            'vProd'  => ['metodo' => 'setVProd', 'tipo' => 'float', 'opcional' => true],
            'vSeg'   => ['metodo' => 'setVSeg', 'tipo' => 'float', 'opcional' => true],
            'vII'    => ['metodo' => 'setVII', 'tipo' => 'float', 'opcional' => true],
            'vIPI'   => ['metodo' => 'setVIPI', 'tipo' => 'float', 'opcional' => true],
            'vPIS'   => ['metodo' => 'setVPIS', 'tipo' => 'float', 'opcional' => true],
            'vCOFINS' => ['metodo' => 'setVCOFINS', 'tipo' => 'float', 'opcional' => true],
            'vOutro' => ['metodo' => 'setVOutro', 'tipo' => 'float', 'opcional' => true],
            'vTotTrib' => ['metodo' => 'setVTotTrib', 'tipo' => 'float', 'opcional' => true]
        ];

        $this->processarCamposComNamespace($total, $camposObrigatorios, false);
        $this->processarCamposComNamespace($total, $camposOpcionais, true);
    }

    /**
     * Processa campos considerando namespaces
     */
    private function processarCamposComNamespace(SimpleXMLElement $elemento, array $campos, bool $camposOpcionais): void
    {
        foreach ($campos as $campo => $config) {
            $valor = null;
            $encontrado = false;

            // Tenta várias maneiras de obter o valor do campo
            if (isset($elemento->$campo) && (string)$elemento->$campo !== '') {
                $valor = $elemento->$campo;
                $encontrado = true;
            } else {
                // Tenta com namespace
                $children = $elemento->children($this->nfeNamespace);
                if (isset($children->$campo) && (string)$children->$campo !== '') {
                    $valor = $children->$campo;
                    $encontrado = true;
                } else {
                    $nodes = $elemento->xpath("./*[local-name()='$campo']");
                    if (!empty($nodes)) {
                        $valor = $nodes[0];
                        $encontrado = true;
                    }
                }
            }

            if ($encontrado) {
                try {
                    $valorConvertido = $this->converterValor($valor, $config['tipo'] ?? 'string');

                    // CORREÇÃO: Removida a opção de adicionar aspas ao valor
                    // Antes: if (isset($config['quote']) && $config['quote']) { $valorConvertido = "'$valorConvertido'"; }
                    // Agora: sempre passa o valor sem aspas adicionais

                    $this->persistencia->{$config['metodo']}($valorConvertido);
                } catch (\Exception $e) {
                    $mensagemErro = "Erro ao processar campo '$campo': " . $e->getMessage();
                    Log::channel('nfe')->error($mensagemErro);

                    if (!$camposOpcionais) {
                        throw new RuntimeException($mensagemErro);
                    }
                }
            } elseif (!$camposOpcionais) {
                $mensagemErro = "Campo obrigatório '$campo' não encontrado";
                Log::channel('nfe')->error($mensagemErro);
                throw new RuntimeException($mensagemErro);
            }
        }
    }

    /**
     * Processa dados do transporte
     */
    private function processarTransporte(?SimpleXMLElement $transp): void
    {
        if (!$transp) {
            return;
        }

        $index = 0; // Use um contador para múltiplas transportadoras, se necessário

        try {
            $modFreteNode = $this->encontrarNoFilho($transp, 'modFrete');
            if ($modFreteNode) {
                $this->persistencia->setModFreteNfeTransportadora($index, (int)$modFreteNode);
            }

            // Processa dados da transportadora
            $transportaNode = $this->encontrarNoFilho($transp, 'transporta');
            if ($transportaNode) {
                $camposTransportadora = [
                    'xNome'  => ['metodo' => 'setXNomeNfeTransportadora'],
                    'xEnder' => ['metodo' => 'setXEnderNfeTransportadora'],
                    'xMun'   => ['metodo' => 'setXMunNfeTransportadora'],
                    'UF'     => ['metodo' => 'setUFNfeTransportadora']
                ];

                foreach ($camposTransportadora as $campo => $config) {
                    $campoNode = $this->encontrarNoFilho($transportaNode, $campo);
                    if ($campoNode && (string)$campoNode !== '') {
                        $this->persistencia->{$config['metodo']}($index, (string)$campoNode);
                    }
                }
            }

            // Processa volumes
            $volNode = $this->encontrarNoFilho($transp, 'vol');
            if ($volNode) {
                $camposVolume = [
                    'qVol'  => ['metodo' => 'setQVolNfeTransportadora', 'tipo' => 'int'],
                    'marca' => ['metodo' => 'setMarcaNfeTransportadora'],
                    'nVol'  => ['metodo' => 'setNVolNfeTransportadora'],
                    'pesoL' => ['metodo' => 'setPesoLNfeTransportadora', 'tipo' => 'float'],
                    'pesoB' => ['metodo' => 'setPesoBNfeTransportadora', 'tipo' => 'float']
                ];

                foreach ($camposVolume as $campo => $config) {
                    $campoNode = $this->encontrarNoFilho($volNode, $campo);
                    if ($campoNode && (string)$campoNode !== '') {
                        $valor = $this->converterValor($campoNode, $config['tipo'] ?? 'string');
                        $this->persistencia->{$config['metodo']}($index, $valor);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::channel('nfe')->warning("Erro ao processar dados do transporte: " . $e->getMessage());
        }
    }

    /**
     * Processa dados de cobrança
     */
    private function processarCobranca(?SimpleXMLElement $cobr): void
    {
        if (!$cobr) {
            return;
        }

        try {
            $fatNode = $this->encontrarNoFilho($cobr, 'fat');
            if (!$fatNode) {
                return;
            }

            $index = 0; // Índice para a fatura

            $nFatNode = $this->encontrarNoFilho($fatNode, 'nFat');
            if ($nFatNode) {
                $this->persistencia->setnFatNfeFatura($index, (int)$nFatNode);
            }

            $vOrigNode = $this->encontrarNoFilho($fatNode, 'vOrig');
            if ($vOrigNode) {
                $this->persistencia->setvOrigNfeFatura($index, (float)$vOrigNode);
            }

            $vLiqNode = $this->encontrarNoFilho($fatNode, 'vLiq');
            if ($vLiqNode) {
                $this->persistencia->setvLiqNfeFatura($index, (float)$vLiqNode);
            }

            // Processar duplicatas
            $dupNodes = $this->encontrarNosFilhos($cobr, 'dup');
            foreach ($dupNodes as $dup) {
                $nDupNode = $this->encontrarNoFilho($dup, 'nDup');
                if ($nDupNode) {
                    $this->persistencia->setnDupNfeFatura($index, (float)$nDupNode);
                }

                $dVencNode = $this->encontrarNoFilho($dup, 'dVenc');
                if ($dVencNode) {
                    $this->persistencia->setdVencNfeFatura($index, (string)$dVencNode);
                }

                $vDupNode = $this->encontrarNoFilho($dup, 'vDup');
                if ($vDupNode) {
                    $this->persistencia->setvDupNfeFatura($index, (float)$vDupNode);
                }
            }
        } catch (\Exception $e) {
            Log::channel('nfe')->warning("Erro ao processar dados de cobrança: " . $e->getMessage());
        }
    }

    /**
     * Processa dados da assinatura digital
     */
    private function processarAssinatura(SimpleXMLElement $signature): void
    {
        try {
            $signatureValueNode = $this->encontrarNoFilho($signature, 'SignatureValue');
            if ($signatureValueNode) {
                $this->persistencia->setSignatureValue((string)$signatureValueNode);
            }

            $signedInfoNode = $this->encontrarNoFilho($signature, 'SignedInfo');
            if ($signedInfoNode) {
                $canonMethodNode = $this->encontrarNoFilho($signedInfoNode, 'CanonicalizationMethod');
                if ($canonMethodNode && isset($canonMethodNode['Algorithm'])) {
                    $this->persistencia->setCanonicalizationMethod((string)$canonMethodNode['Algorithm']);
                }

                $signMethodNode = $this->encontrarNoFilho($signedInfoNode, 'SignatureMethod');
                if ($signMethodNode && isset($signMethodNode['Algorithm'])) {
                    $this->persistencia->setSignatureMethod((string)$signMethodNode['Algorithm']);
                }

                $referenceNode = $this->encontrarNoFilho($signedInfoNode, 'Reference');
                if ($referenceNode) {
                    if (isset($referenceNode['URI'])) {
                        $this->persistencia->setReference((string)$referenceNode['URI']);
                    }

                    $digestValueNode = $this->encontrarNoFilho($referenceNode, 'DigestValue');
                    if ($digestValueNode) {
                        $this->persistencia->setDigestValue((string)$digestValueNode);
                    }

                    $digestMethodNode = $this->encontrarNoFilho($referenceNode, 'DigestMethod');
                    if ($digestMethodNode && isset($digestMethodNode['Algorithm'])) {
                        $this->persistencia->setDigestMethod((string)$digestMethodNode['Algorithm']);
                    }
                }
            }

            $keyInfoNode = $this->encontrarNoFilho($signature, 'KeyInfo');
            if ($keyInfoNode) {
                $x509DataNodes = $this->encontrarNosFilhos($keyInfoNode, 'X509Data');
                foreach ($x509DataNodes as $x509Data) {
                    $x509CertNode = $this->encontrarNoFilho($x509Data, 'X509Certificate');
                    if ($x509CertNode) {
                        $this->persistencia->setX509Certificate((string)$x509CertNode);
                        break; // Apenas o primeiro certificado
                    }
                }
            }
        } catch (\Exception $e) {
            Log::channel('nfe')->warning("Erro ao processar dados da assinatura: " . $e->getMessage());
        }
    }

    /**
     * Processa campos genéricos do XML
     */
    private function processarCampos(SimpleXMLElement $elemento, array $campos, bool $camposOpcionais): void
    {
        foreach ($campos as $campo => $config) {
            if (isset($elemento->$campo) && (string)$elemento->$campo !== '') {
                try {
                    $valor = $this->converterValor($elemento->$campo, $config['tipo'] ?? 'string');

                    // CORREÇÃO: Removida a opção de adicionar aspas ao valor
                    // Antes: if (isset($config['quote']) && $config['quote']) { $valor = "'$valor'"; }
                    // Agora: sempre passa o valor sem aspas adicionais

                    $this->persistencia->{$config['metodo']}($valor);
                } catch (\Exception $e) {
                    $mensagemErro = "Erro ao processar campo '$campo': " . $e->getMessage();
                    Log::channel('nfe')->error($mensagemErro);

                    if (!$camposOpcionais) {
                        throw new RuntimeException($mensagemErro);
                    }
                }
            } elseif (!$camposOpcionais) {
                $mensagemErro = "Campo obrigatório '$campo' não encontrado";
                Log::channel('nfe')->error($mensagemErro);
                throw new RuntimeException($mensagemErro);
            }
        }
    }

    /**
     * Converte valor para o tipo especificado com tratamento adequado de erros
     */
    private function converterValor($valor, string $tipo)
    {
        if ($valor === null) {
            return null;
        }

        $valorString = (string)$valor;

        if ($valorString === '') {
            if ($tipo === 'int') {
                return 0;
            } elseif ($tipo === 'float') {
                return 0.0;
            } else {
                return '';
            }
        }

        try {
            switch ($tipo) {
                case 'int':
                    // Remove caracteres não numéricos para evitar erros
                    $valorLimpo = preg_replace('/[^0-9-]/', '', $valorString);
                    return (int)$valorLimpo;
                case 'float':
                    // Trata possíveis problemas com separadores decimais
                    $valorLimpo = str_replace(',', '.', $valorString);
                    return (float)$valorLimpo;
                default:
                    return $valorString;
            }
        } catch (\Exception $e) {
            Log::channel('nfe')->warning("Erro ao converter valor '$valorString' para $tipo: " . $e->getMessage());
            // Retorna um valor padrão para evitar erros
            if ($tipo === 'int') {
                return 0;
            } elseif ($tipo === 'float') {
                return 0.0;
            } else {
                return '';
            }
        }
    }
}
