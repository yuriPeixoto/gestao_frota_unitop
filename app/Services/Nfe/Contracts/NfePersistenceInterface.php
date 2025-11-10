<?php

namespace App\Services\Nfe\Contracts;

interface NfePersistenceInterface
{
    /**
     * Insere uma NFe no banco de dados.
     *
     * @return array Resultado da inserção
     */
    public function insert(): array;

    /**
     * Verifica se uma NFe já existe no banco de dados.
     *
     * @param string $infNFe
     * @return bool
     */
    public function nfeJaExiste(string $infNFe): bool;

    // Setters para Dados Básicos da NFe
    public function setInfNFe(string $infNFe): self;
    public function setCUF(int $cUF): self;
    public function setCNF(int $cNF): self;
    public function setNatOp(string $natOp): self;
    public function setIndPag(?int $indPag): self;
    public function setMod(int $mod): self;
    public function setSerie(int $serie): self;
    public function setNNF(int $nNF): self;
    public function setDhEmi(string $dhEmi): self;
    public function setDhSaiEnt(string $dhSaiEnt): self;
    public function setTpNF(int $tpNF): self;
    public function setIdDest(?int $idDest): self;
    public function setCMunFG(?int $cMunFG): self;
    public function setTpImp(int $tpImp): self;
    public function setTpEmis(int $tpEmis): self;
    public function setCDV(int $cDV): self;
    public function setTpAmb(int $tpAmb): self;
    public function setFinNFe(int $finNFe): self;
    public function setIndFinal(int $indFinal): self;
    public function setIndPres(int $indPres): self;
    public function setProcEmi(int $procEmi): self;
    public function setVerProc(string $verProc): self;

    // Setters para Valores
    public function setVBC(float $vBC): self;
    public function setVICMS(float $vICMS): self;
    public function setVICMSDeson(float $vICMSDeson): self;
    public function setVBCST(float $vBCST): self;
    public function setVST(float $vST): self;
    public function setVProd(float $vProd): self;
    public function setVFrete(float $vFrete): self;
    public function setVSeg(float $vSeg): self;
    public function setVDesc(float $vDesc): self;
    public function setVII(float $vII): self;
    public function setVIPI(float $vIPI): self;
    public function setVPIS(float $vPIS): self;
    public function setVCOFINS(float $vCOFINS): self;
    public function setVOutro(float $vOutro): self;
    public function setVNF(float $vNF): self;
    public function setVTotTrib(float $vTotTrib): self;

    // Setters para Informações Complementares
    public function setInfCpl(string $infCpl): self;

    // Setters para Assinatura Digital
    public function setSignature(string $signature): self;
    public function setCanonicalizationMethod(string $method): self;
    public function setSignatureMethod(string $method): self;
    public function setReference(string $reference): self;
    public function setDigestMethod(string $method): self;
    public function setDigestValue(string $value): self;
    public function setSignatureValue(string $value): self;
    public function setX509Certificate(string $certificate): self;

    // Setters para Emissor
    public function setIdNfeEmissor(int $id): self;
    public function setCnpjNfeEmissor(string $cnpj): self;
    public function setIeNfeEmissor(int $ie): self;
    public function setCrtNfeEmissor(int $crt): self;
    public function setxNomeNfeEmissor(string $nome): self;
    public function setxFantNfeEmissor(string $fantasia): self;
    public function setxLgrNfeEmissor(string $logradouro): self;
    public function setNroNfeEmissor(int $numero): self;
    public function setxBairroNfeEmissor(string $bairro): self;
    public function setcMunNfeEmissor(int $codigoMunicipio): self;
    public function setxMunNfeEmissor(string $municipio): self;
    public function setUfNfeEmissor(string $uf): self;
    public function setCepNfeEmissor(int $cep): self;
    public function setcPaisNfeEmissor(int $codigoPais): self;
    public function setxPaisNfeEmissor(string $pais): self;
    public function setFoneNfeEmissor(string $telefone): self;

    // Setters para Destinatário
    public function setIdNfeDestinatario(int $id): self;
    public function setCnpjNfeDestinatario(string $cnpj): self;
    public function setCpfNfeDestinatario(string $cpf): self;
    public function setXNomeNfeDestinatario(string $nome): self;
    public function setXLgrNfeDestinatario(string $logradouro): self;
    public function setNroNfeDestinatario(int $numero): self;
    public function setXBairroNfeDestinatario(string $bairro): self;
    public function setCMunNfeDestinatario(int $codigoMunicipio): self;
    public function setXMunNfeDestinatario(string $municipio): self;
    public function setUfNfeDestinatario(string $uf): self;
    public function setCepNfeDestinatario(int $cep): self;
    public function setCPaisNfeDestinatario(int $codigoPais): self;
    public function setXPaisNfeDestinatario(string $pais): self;
    public function setFoneNfeDestinatario(string $telefone): self;
    public function setIndIEDestNfeDestinatario(string $indicadorIE): self;
    public function setEmailNfeDestinatario(string $email): self;

    // Setters para Produtos
    public function setidNfeProduto(int $id): self;
    public function setnItemProduto(int $nItem): self;
    public function setcProdProduto(int $nItem, string $codigo): self;
    public function setcEANProduto(int $nItem, string $ean): self;
    public function setxProdProduto(int $nItem, string $descricao): self;
    public function setNCMProduto(int $nItem, int $ncm): self;
    public function setCFOPProduto(int $nItem, int $cfop): self;
    public function setuComProduto(int $nItem, string $unidadeComercial): self;
    public function setqComProduto(int $nItem, float $quantidade): self;
    public function setvUnComProduto(int $nItem, float $valorUnitario): self;
    public function setvProdProduto(int $nItem, float $valorTotal): self;
    public function setcEANTribProduto(int $nItem, string $eanTrib): self;
    public function setuTribProduto(int $nItem, string $unidadeTributavel): self;
    public function setqTribProduto(int $nItem, float $quantidadeTributavel): self;
    public function setvUnTribProduto(int $nItem, float $valorUnitarioTrib): self;
    public function setvTotTribProduto(int $nItem, float $valorTotalTrib): self;
    public function setorigProduto(int $nItem, int $origem): self;
    public function setCSOSNProduto(int $nItem, int $csosn): self;
    public function setindTotProduto(int $nItem, int $indicadorTotal): self;
    public function setpCredSNProduto(int $nItem, float $percentualCredito): self;
    public function setvCredICMSSNProduto(int $nItem, float $valorCredito): self;
    /**
     * Define a classe de enquadramento do produto.
     *
     * @param int $nItem
     * @param int $clEnq
     * @return self
     */
    public function setclEnqProduto(int $nItem, int $clEnq): self;
    /**
     * Define o CNPJ do produtor do produto.
     *
     * @param int $nItem
     * @param int $cnpj
     * @return self
     */
    public function setCNPJProdProduto(int $nItem, int $cnpj): self;
    /**
     * Define o código de enquadramento do produto.
     *
     * @param int $nItem
     * @param int $cEnq
     * @return self
     */
    public function setcEnqProduto(int $nItem, int $cEnq): self;
    public function setCST1Produto(int $nItem, int $cst1): self;
    public function setCST2Produto(int $nItem, int $cst2): self;
    public function setCST3Produto(int $nItem, int $cst3): self;
    public function setvDescProduto(int $nItem, float $valorDesconto): self;

    // Setters para Transportadora
    public function setIdNfeTransportadora(int $index, int $id): self;
    public function setModFreteNfeTransportadora(int $index, int $modalidade): self;
    public function setXNomeNfeTransportadora(int $index, string $nome): self;
    public function setXEnderNfeTransportadora(int $index, string $endereco): self;
    public function setXMunNfeTransportadora(int $index, string $municipio): self;
    public function setUFNfeTransportadora(int $index, string $uf): self;
    public function setQVolNfeTransportadora(int $index, int $quantidade): self;
    public function setMarcaNfeTransportadora(int $index, string $marca): self;
    public function setNVolNfeTransportadora(int $index, string $numeracao): self;
    public function setPesoLNfeTransportadora(int $index, float $pesoLiquido): self;
    public function setPesoBNfeTransportadora(int $index, float $pesoBruto): self;

    // Setters para Fatura
    public function setnFatNfeFatura(int $index, int $numero): self;
    public function setvOrigNfeFatura(int $index, float $valorOriginal): self;
    public function setvLiqNfeFatura(int $index, float $valorLiquido): self;
    public function setnDupNfeFatura(int $index, float $numeroDuplicata): self;
    public function setdVencNfeFatura(int $index, string $dataVencimento): self;
    public function setvDupNfeFatura(int $index, float $valorDuplicata): self;
}
