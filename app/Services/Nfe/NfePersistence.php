<?php

namespace App\Services\Nfe;

use App\Services\Nfe\Contracts\NfePersistenceInterface;
use App\Models\NfeCore;
use App\Models\NfeEmissor;
use App\Models\NfeDestinatario;
use App\Models\NfeProduto;
use App\Models\NfeTransportadora;
use App\Models\NfeFatura;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NfePersistence implements NfePersistenceInterface
{
    // Atributos básicos da NFe
    private array $attributes = [];

    // Atributos para o emissor
    private array $emissorAttributes = [];

    // Atributos para o destinatário
    private array $destinatarioAttributes = [];

    // Arrays para os itens relacionados
    private array $produtos = [];
    private array $transportadoras = [];
    private array $faturas = [];

    /**
     * Insere uma NFe no banco de dados.
     *
     * @return array Resultado da inserção
     */
    public function insert(): array
    {
        try {
            // Verifica se já existe
            if ($this->nfeJaExiste($this->attributes['infnfe'] ?? '')) {
                Log::channel('nfe')->warning("NFe {$this->attributes['infnfe']} já existe no banco de dados.");
                return [
                    'success' => true,
                    'warning' => "NFe {$this->attributes['infnfe']} já existe no banco de dados."
                ];
            }

            DB::beginTransaction();

            // Cria o registro principal
            $nfe = NfeCore::create($this->attributes);

            // Insere o emissor - GARANTE que o ID não está presente para evitar conflitos
            if (!empty($this->emissorAttributes)) {
                // Remover o ID se estiver presente nos atributos
                if (isset($this->emissorAttributes['id'])) {
                    Log::channel('nfe')->info("Removendo ID do emissor: " . $this->emissorAttributes['id']);
                    unset($this->emissorAttributes['id']);
                }

                $this->emissorAttributes['id_nfe'] = $nfe->id;
                NfeEmissor::create($this->emissorAttributes);
            }

            // Insere o destinatário - GARANTE que o ID não está presente
            if (!empty($this->destinatarioAttributes)) {
                if (isset($this->destinatarioAttributes['id'])) {
                    Log::channel('nfe')->info("Removendo ID do destinatário: " . $this->destinatarioAttributes['id']);
                    unset($this->destinatarioAttributes['id']);
                }

                $this->destinatarioAttributes['id_nfe'] = $nfe->id;
                NfeDestinatario::create($this->destinatarioAttributes);
            }

            // Insere os produtos - GARANTE que o ID não está presente
            foreach ($this->produtos as $k => $produto) {
                if (isset($produto['id'])) {
                    Log::channel('nfe')->info("Removendo ID do produto: " . $produto['id']);
                    unset($this->produtos[$k]['id']);
                }

                $this->produtos[$k]['id_nfe'] = $nfe->id;
                NfeProduto::create($this->produtos[$k]);
            }

            // Insere as transportadoras - GARANTE que o ID não está presente
            foreach ($this->transportadoras as $k => $transportadora) {
                if (isset($transportadora['id'])) {
                    Log::channel('nfe')->info("Removendo ID da transportadora: " . $transportadora['id']);
                    unset($this->transportadoras[$k]['id']);
                }

                $this->transportadoras[$k]['id_nfe'] = $nfe->id;
                NfeTransportadora::create($this->transportadoras[$k]);
            }

            // Insere as faturas - GARANTE que o ID não está presente
            foreach ($this->faturas as $k => $fatura) {
                if (isset($fatura['id'])) {
                    Log::channel('nfe')->info("Removendo ID da fatura: " . $fatura['id']);
                    unset($this->faturas[$k]['id']);
                }

                $this->faturas[$k]['id_nfe'] = $nfe->id;
                NfeFatura::create($this->faturas[$k]);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'NFe inserida com sucesso',
                'nfe_id' => $nfe->id
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('nfe')->error("Erro ao inserir NFe: " . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verifica se uma NFe já existe no banco de dados.
     *
     * @param string $infNFe
     * @return bool
     */
    public function nfeJaExiste(string $infNFe): bool
    {
        return NfeCore::where('infnfe', 'ILIKE', $infNFe)->exists();
    }

    /**
     * Define o valor para infNFe.
     *
     * @param string $infNFe
     * @return self
     */
    public function setInfNFe(string $infNFe): self
    {
        $this->attributes['infnfe'] = $infNFe;
        return $this;
    }

    /**
     * Define o valor para cUF.
     *
     * @param int $cUF
     * @return self
     */
    public function setCUF(int $cUF): self
    {
        $this->attributes['cuf'] = $cUF;
        return $this;
    }

    /**
     * Define o valor para cNF.
     *
     * @param int $cNF
     * @return self
     */
    public function setCNF(int $cNF): self
    {
        $this->attributes['cnf'] = $cNF;
        return $this;
    }

    /**
     * Define o valor para natOp.
     *
     * @param string $natOp
     * @return self
     */
    public function setNatOp(string $natOp): self
    {
        $this->attributes['natop'] = $natOp;
        return $this;
    }

    /**
     * Define o valor para indPag.
     *
     * @param int|null $indPag
     * @return self
     */
    public function setIndPag(?int $indPag): self
    {
        $this->attributes['indpag'] = $indPag;
        return $this;
    }

    /**
     * Define o valor para mod.
     *
     * @param int $mod
     * @return self
     */
    public function setMod(int $mod): self
    {
        $this->attributes['mod'] = $mod;
        return $this;
    }

    /**
     * Define o valor para serie.
     *
     * @param int $serie
     * @return self
     */
    public function setSerie(int $serie): self
    {
        $this->attributes['serie'] = $serie;
        return $this;
    }

    /**
     * Define o valor para nNF.
     *
     * @param int $nNF
     * @return self
     */
    public function setNNF(int $nNF): self
    {
        $this->attributes['nnf'] = $nNF;
        return $this;
    }

    /**
     * Define o valor para dhEmi.
     *
     * @param string $dhEmi
     * @return self
     */
    public function setDhEmi(string $dhEmi): self
    {
        $this->attributes['dhemi'] = $dhEmi;
        return $this;
    }

    /**
     * Define o valor para dhSaiEnt.
     *
     * @param string $dhSaiEnt
     * @return self
     */
    public function setDhSaiEnt(string $dhSaiEnt): self
    {
        $this->attributes['dhsaient'] = $dhSaiEnt;
        return $this;
    }

    /**
     * Define o valor para tpNF.
     *
     * @param int $tpNF
     * @return self
     */
    public function setTpNF(int $tpNF): self
    {
        $this->attributes['tpnf'] = $tpNF;
        return $this;
    }

    /**
     * Define o valor para idDest.
     *
     * @param int|null $idDest
     * @return self
     */
    public function setIdDest(?int $idDest): self
    {
        $this->attributes['iddest'] = $idDest;
        return $this;
    }

    /**
     * Define o valor para cMunFG.
     *
     * @param int|null $cMunFG
     * @return self
     */
    public function setCMunFG(?int $cMunFG): self
    {
        $this->attributes['cmunfg'] = $cMunFG;
        return $this;
    }

    /**
     * Define o valor para tpImp.
     *
     * @param int $tpImp
     * @return self
     */
    public function setTpImp(int $tpImp): self
    {
        $this->attributes['tpimp'] = $tpImp;
        return $this;
    }

    /**
     * Define o valor para tpEmis.
     *
     * @param int $tpEmis
     * @return self
     */
    public function setTpEmis(int $tpEmis): self
    {
        $this->attributes['tpemis'] = $tpEmis;
        return $this;
    }

    /**
     * Define o valor para cDV.
     *
     * @param int $cDV
     * @return self
     */
    public function setCDV(int $cDV): self
    {
        $this->attributes['cdv'] = $cDV;
        return $this;
    }

    /**
     * Define o valor para tpAmb.
     *
     * @param int $tpAmb
     * @return self
     */
    public function setTpAmb(int $tpAmb): self
    {
        $this->attributes['tpamb'] = $tpAmb;
        return $this;
    }

    /**
     * Define o valor para finNFe.
     *
     * @param int $finNFe
     * @return self
     */
    public function setFinNFe(int $finNFe): self
    {
        $this->attributes['finnfe'] = $finNFe;
        return $this;
    }

    /**
     * Define o valor para indFinal.
     *
     * @param int $indFinal
     * @return self
     */
    public function setIndFinal(int $indFinal): self
    {
        $this->attributes['indfinal'] = $indFinal;
        return $this;
    }

    /**
     * Define o valor para indPres.
     *
     * @param int $indPres
     * @return self
     */
    public function setIndPres(int $indPres): self
    {
        $this->attributes['indpres'] = $indPres;
        return $this;
    }

    /**
     * Define o valor para procEmi.
     *
     * @param int $procEmi
     * @return self
     */
    public function setProcEmi(int $procEmi): self
    {
        $this->attributes['procemi'] = $procEmi;
        return $this;
    }

    /**
     * Define o valor para verProc.
     *
     * @param string $verProc
     * @return self
     */
    public function setVerProc(string $verProc): self
    {
        $this->attributes['verproc'] = $verProc;
        return $this;
    }

    /**
     * Define o valor para vBC.
     *
     * @param float $vBC
     * @return self
     */
    public function setVBC(float $vBC): self
    {
        $this->attributes['vbc'] = $vBC;
        return $this;
    }

    /**
     * Define o valor para vICMS.
     *
     * @param float $vICMS
     * @return self
     */
    public function setVICMS(float $vICMS): self
    {
        $this->attributes['vicms'] = $vICMS;
        return $this;
    }

    /**
     * Define o valor para vICMSDeson.
     *
     * @param float $vICMSDeson
     * @return self
     */
    public function setVICMSDeson(float $vICMSDeson): self
    {
        $this->attributes['vicmsdeson'] = $vICMSDeson;
        return $this;
    }

    /**
     * Define o valor para vBCST.
     *
     * @param float $vBCST
     * @return self
     */
    public function setVBCST(float $vBCST): self
    {
        $this->attributes['vbcst'] = $vBCST;
        return $this;
    }

    /**
     * Define o valor para vST.
     *
     * @param float $vST
     * @return self
     */
    public function setVST(float $vST): self
    {
        $this->attributes['vst'] = $vST;
        return $this;
    }

    /**
     * Define o valor para vProd.
     *
     * @param float $vProd
     * @return self
     */
    public function setVProd(float $vProd): self
    {
        $this->attributes['vprod'] = $vProd;
        return $this;
    }

    /**
     * Define o valor para vFrete.
     *
     * @param float $vFrete
     * @return self
     */
    public function setVFrete(float $vFrete): self
    {
        $this->attributes['vfrete'] = $vFrete;
        return $this;
    }

    /**
     * Define o valor para vSeg.
     *
     * @param float $vSeg
     * @return self
     */
    public function setVSeg(float $vSeg): self
    {
        $this->attributes['vseg'] = $vSeg;
        return $this;
    }

    /**
     * Define o valor para vDesc.
     *
     * @param float $vDesc
     * @return self
     */
    public function setVDesc(float $vDesc): self
    {
        $this->attributes['vdesc'] = $vDesc;
        return $this;
    }

    /**
     * Define o valor para vII.
     *
     * @param float $vII
     * @return self
     */
    public function setVII(float $vII): self
    {
        $this->attributes['vii'] = $vII;
        return $this;
    }

    /**
     * Define o valor para vIPI.
     *
     * @param float $vIPI
     * @return self
     */
    public function setVIPI(float $vIPI): self
    {
        $this->attributes['vipi'] = $vIPI;
        return $this;
    }

    /**
     * Define o valor para vPIS.
     *
     * @param float $vPIS
     * @return self
     */
    public function setVPIS(float $vPIS): self
    {
        $this->attributes['vpis'] = $vPIS;
        return $this;
    }

    /**
     * Define o valor para vCOFINS.
     *
     * @param float $vCOFINS
     * @return self
     */
    public function setVCOFINS(float $vCOFINS): self
    {
        $this->attributes['vcofins'] = $vCOFINS;
        return $this;
    }

    /**
     * Define o valor para vOutro.
     *
     * @param float $vOutro
     * @return self
     */
    public function setVOutro(float $vOutro): self
    {
        $this->attributes['voutro'] = $vOutro;
        return $this;
    }

    /**
     * Define o valor para vNF.
     *
     * @param float $vNF
     * @return self
     */
    public function setVNF(float $vNF): self
    {
        $this->attributes['vnf'] = $vNF;
        return $this;
    }

    /**
     * Define o valor para vTotTrib.
     *
     * @param float $vTotTrib
     * @return self
     */
    public function setVTotTrib(float $vTotTrib): self
    {
        $this->attributes['vtottrib'] = $vTotTrib;
        return $this;
    }

    /**
     * Define o valor para infCpl.
     *
     * @param string $infCpl
     * @return self
     */
    public function setInfCpl(string $infCpl): self
    {
        $this->attributes['infcpl'] = $infCpl;
        return $this;
    }

    /**
     * Define o valor para Signature.
     *
     * @param string $signature
     * @return self
     */
    public function setSignature(string $signature): self
    {
        $this->attributes['signature'] = $signature;
        return $this;
    }

    /**
     * Define o valor para CanonicalizationMethod.
     *
     * @param string $method
     * @return self
     */
    public function setCanonicalizationMethod(string $method): self
    {
        $this->attributes['canonicalizationmethod'] = $method;
        return $this;
    }

    /**
     * Define o valor para SignatureMethod.
     *
     * @param string $method
     * @return self
     */
    public function setSignatureMethod(string $method): self
    {
        $this->attributes['signaturemethod'] = $method;
        return $this;
    }

    /**
     * Define o valor para Reference.
     *
     * @param string $reference
     * @return self
     */
    public function setReference(string $reference): self
    {
        $this->attributes['reference'] = $reference;
        return $this;
    }

    /**
     * Define o valor para DigestMethod.
     *
     * @param string $method
     * @return self
     */
    public function setDigestMethod(string $method): self
    {
        $this->attributes['digestmethod'] = $method;
        return $this;
    }

    /**
     * Define o valor para DigestValue.
     *
     * @param string $value
     * @return self
     */
    public function setDigestValue(string $value): self
    {
        $this->attributes['digestvalue'] = $value;
        return $this;
    }

    /**
     * Define o valor para SignatureValue.
     *
     * @param string $value
     * @return self
     */
    public function setSignatureValue(string $value): self
    {
        $this->attributes['signaturevalue'] = $value;
        return $this;
    }

    /**
     * Define o valor para X509Certificate.
     *
     * @param string $certificate
     * @return self
     */
    public function setX509Certificate(string $certificate): self
    {
        $this->attributes['x509certificate'] = $certificate;
        return $this;
    }

    /**
     * Define o valor para IdNfeEmissor.
     *
     * @param int $id
     * @return self
     */
    public function setIdNfeEmissor(int $id): self
    {
        // Registramos no log mas não definimos o ID para evitar conflitos
        Log::channel('nfe')->debug("Tentativa de definir ID do emissor ignorada: $id");
        return $this;
    }

    /**
     * Define o valor para CnpjNfeEmissor.
     *
     * @param string $cnpj
     * @return self
     */
    public function setCnpjNfeEmissor(string $cnpj): self
    {
        $this->emissorAttributes['cnpj'] = $cnpj;
        return $this;
    }

    /**
     * Define o valor para IeNfeEmissor.
     *
     * @param int $ie
     * @return self
     */
    public function setIeNfeEmissor(int $ie): self
    {
        $this->emissorAttributes['ie'] = $ie;
        return $this;
    }

    /**
     * Define o valor para CrtNfeEmissor.
     *
     * @param int $crt
     * @return self
     */
    public function setCrtNfeEmissor(int $crt): self
    {
        $this->emissorAttributes['crt'] = $crt;
        return $this;
    }

    /**
     * Define o valor para xNomeNfeEmissor.
     *
     * @param string $nome
     * @return self
     */
    public function setxNomeNfeEmissor(string $nome): self
    {
        $this->emissorAttributes['xnome'] = $nome;
        return $this;
    }

    /**
     * Define o valor para xFantNfeEmissor.
     *
     * @param string $fantasia
     * @return self
     */
    public function setxFantNfeEmissor(string $fantasia): self
    {
        $this->emissorAttributes['xfant'] = $fantasia;
        return $this;
    }

    /**
     * Define o valor para xLgrNfeEmissor.
     *
     * @param string $logradouro
     * @return self
     */
    public function setxLgrNfeEmissor(string $logradouro): self
    {
        $this->emissorAttributes['xlgr'] = $logradouro;
        return $this;
    }

    /**
     * Define o valor para NroNfeEmissor.
     *
     * @param int $numero
     * @return self
     */
    public function setNroNfeEmissor(int $numero): self
    {
        $this->emissorAttributes['nro'] = $numero;
        return $this;
    }

    /**
     * Define o valor para xBairroNfeEmissor.
     *
     * @param string $bairro
     * @return self
     */
    public function setxBairroNfeEmissor(string $bairro): self
    {
        $this->emissorAttributes['xbairro'] = $bairro;
        return $this;
    }

    /**
     * Define o valor para cMunNfeEmissor.
     *
     * @param int $codigoMunicipio
     * @return self
     */
    public function setcMunNfeEmissor(int $codigoMunicipio): self
    {
        $this->emissorAttributes['cmun'] = $codigoMunicipio;
        return $this;
    }

    /**
     * Define o valor para xMunNfeEmissor.
     *
     * @param string $municipio
     * @return self
     */
    public function setxMunNfeEmissor(string $municipio): self
    {
        $this->emissorAttributes['xmun'] = $municipio;
        return $this;
    }

    /**
     * Define o valor para UfNfeEmissor.
     *
     * @param string $uf
     * @return self
     */
    public function setUfNfeEmissor(string $uf): self
    {
        $this->emissorAttributes['uf'] = $uf;
        return $this;
    }

    /**
     * Define o valor para CepNfeEmissor.
     *
     * @param int $cep
     * @return self
     */
    public function setCepNfeEmissor(int $cep): self
    {
        $this->emissorAttributes['cep'] = $cep;
        return $this;
    }

    /**
     * Define o valor para cPaisNfeEmissor.
     *
     * @param int $codigoPais
     * @return self
     */
    public function setcPaisNfeEmissor(int $codigoPais): self
    {
        $this->emissorAttributes['cpais'] = $codigoPais;
        return $this;
    }

    /**
     * Define o valor para xPaisNfeEmissor.
     *
     * @param string $pais
     * @return self
     */
    public function setxPaisNfeEmissor(string $pais): self
    {
        $this->emissorAttributes['xpais'] = $pais;
        return $this;
    }

    /**
     * Define o valor para FoneNfeEmissor.
     *
     * @param string $telefone
     * @return self
     */
    public function setFoneNfeEmissor(string $telefone): self
    {
        $this->emissorAttributes['fone'] = $telefone;
        return $this;
    }

    /**
     * Define o valor para IdNfeDestinatario.
     *
     * @param int $id
     * @return self
     */
    public function setIdNfeDestinatario(int $id): self
    {
        // Registramos no log mas não definimos o ID para evitar conflitos
        Log::channel('nfe')->debug("Tentativa de definir ID do destinatário ignorada: $id");
        return $this;
    }

    /**
     * Define o valor para CnpjNfeDestinatario.
     *
     * @param string $cnpj
     * @return self
     */
    public function setCnpjNfeDestinatario(string $cnpj): self
    {
        $this->destinatarioAttributes['cnpj'] = $cnpj;
        return $this;
    }

    /**
     * Define o valor para CpfNfeDestinatario.
     *
     * @param string $cpf
     * @return self
     */
    public function setCpfNfeDestinatario(string $cpf): self
    {
        $this->destinatarioAttributes['cpf'] = $cpf;
        return $this;
    }

    /**
     * Define o valor para XNomeNfeDestinatario.
     *
     * @param string $nome
     * @return self
     */
    public function setXNomeNfeDestinatario(string $nome): self
    {
        $this->destinatarioAttributes['xnome'] = $nome;
        return $this;
    }

    /**
     * Define o valor para XLgrNfeDestinatario.
     *
     * @param string $logradouro
     * @return self
     */
    public function setXLgrNfeDestinatario(string $logradouro): self
    {
        $this->destinatarioAttributes['xlgr'] = $logradouro;
        return $this;
    }

    /**
     * Define o valor para NroNfeDestinatario.
     *
     * @param int $numero
     * @return self
     */
    public function setNroNfeDestinatario(int $numero): self
    {
        $this->destinatarioAttributes['nro'] = $numero;
        return $this;
    }

    /**
     * Define o valor para XBairroNfeDestinatario.
     *
     * @param string $bairro
     * @return self
     */
    public function setXBairroNfeDestinatario(string $bairro): self
    {
        $this->destinatarioAttributes['xbairro'] = $bairro;
        return $this;
    }

    /**
     * Define o valor para CMunNfeDestinatario.
     *
     * @param int $codigoMunicipio
     * @return self
     */
    public function setCMunNfeDestinatario(int $codigoMunicipio): self
    {
        $this->destinatarioAttributes['cmun'] = $codigoMunicipio;
        return $this;
    }

    /**
     * Define o valor para XMunNfeDestinatario.
     *
     * @param string $municipio
     * @return self
     */
    public function setXMunNfeDestinatario(string $municipio): self
    {
        $this->destinatarioAttributes['xmun'] = $municipio;
        return $this;
    }

    /**
     * Define o valor para UfNfeDestinatario.
     *
     * @param string $uf
     * @return self
     */
    public function setUfNfeDestinatario(string $uf): self
    {
        $this->destinatarioAttributes['uf'] = $uf;
        return $this;
    }

    /**
     * Define o valor para CepNfeDestinatario.
     *
     * @param int $cep
     * @return self
     */
    public function setCepNfeDestinatario(int $cep): self
    {
        $this->destinatarioAttributes['cep'] = $cep;
        return $this;
    }

    /**
     * Define o valor para CPaisNfeDestinatario.
     *
     * @param int $codigoPais
     * @return self
     */
    public function setCPaisNfeDestinatario(int $codigoPais): self
    {
        $this->destinatarioAttributes['cpais'] = $codigoPais;
        return $this;
    }

    /**
     * Define o valor para XPaisNfeDestinatario.
     *
     * @param string $pais
     * @return self
     */
    public function setXPaisNfeDestinatario(string $pais): self
    {
        $this->destinatarioAttributes['xpais'] = $pais;
        return $this;
    }

    /**
     * Define o valor para FoneNfeDestinatario.
     *
     * @param string $telefone
     * @return self
     */
    public function setFoneNfeDestinatario(string $telefone): self
    {
        $this->destinatarioAttributes['fone'] = $telefone;
        return $this;
    }

    /**
     * Define o valor para IndIEDestNfeDestinatario.
     *
     * @param string $indicadorIE
     * @return self
     */
    public function setIndIEDestNfeDestinatario(string $indicadorIE): self
    {
        $this->destinatarioAttributes['indiedest'] = $indicadorIE;
        return $this;
    }

    /**
     * Define o valor para EmailNfeDestinatario.
     *
     * @param string $email
     * @return self
     */
    public function setEmailNfeDestinatario(string $email): self
    {
        $this->destinatarioAttributes['email'] = $email;
        return $this;
    }

    /**
     * Define o ID do produto.
     *
     * @param int $id
     * @return self
     */
    public function setidNfeProduto(int $id): self
    {
        // Registramos no log mas não definimos o ID para evitar conflitos
        Log::channel('nfe')->debug("Tentativa de definir ID do produto ignorada: $id");
        return $this;
    }

    /**
     * Define o número do item.
     *
     * @param int $nItem
     * @return self
     */
    public function setnItemProduto(int $nItem): self
    {
        if (!isset($this->produtos[$nItem])) {
            $this->produtos[$nItem] = ['nitem' => $nItem];
        }
        return $this;
    }

    /**
     * Define o código do produto.
     *
     * @param int $nItem
     * @param string $codigo
     * @return self
     */
    public function setcProdProduto(int $nItem, string $codigo): self
    {
        $this->produtos[$nItem]['cprod'] = $codigo;
        return $this;
    }

    /**
     * Define o EAN do produto.
     *
     * @param int $nItem
     * @param string $ean
     * @return self
     */
    public function setcEANProduto(int $nItem, string $ean): self
    {
        $this->produtos[$nItem]['cean'] = $ean;
        return $this;
    }

    /**
     * Define a descrição do produto.
     *
     * @param int $nItem
     * @param string $descricao
     * @return self
     */
    public function setxProdProduto(int $nItem, string $descricao): self
    {
        $this->produtos[$nItem]['xprod'] = $descricao;
        return $this;
    }

    /**
     * Define o NCM do produto.
     *
     * @param int $nItem
     * @param int $ncm
     * @return self
     */
    public function setNCMProduto(int $nItem, int $ncm): self
    {
        $this->produtos[$nItem]['ncm'] = $ncm;
        return $this;
    }

    /**
     * Define o CFOP do produto.
     *
     * @param int $nItem
     * @param int $cfop
     * @return self
     */
    public function setCFOPProduto(int $nItem, int $cfop): self
    {
        $this->produtos[$nItem]['cfop'] = $cfop;
        return $this;
    }

    /**
     * Define a unidade comercial do produto.
     *
     * @param int $nItem
     * @param string $unidadeComercial
     * @return self
     */
    public function setuComProduto(int $nItem, string $unidadeComercial): self
    {
        $this->produtos[$nItem]['ucom'] = $unidadeComercial;
        return $this;
    }

    /**
     * Define a quantidade comercial do produto.
     *
     * @param int $nItem
     * @param float $quantidade
     * @return self
     */
    public function setqComProduto(int $nItem, float $quantidade): self
    {
        $this->produtos[$nItem]['qcom'] = $quantidade;
        return $this;
    }

    /**
     * Define o valor unitário comercial do produto.
     *
     * @param int $nItem
     * @param float $valorUnitario
     * @return self
     */
    public function setvUnComProduto(int $nItem, float $valorUnitario): self
    {
        $this->produtos[$nItem]['vuncom'] = $valorUnitario;
        return $this;
    }

    /**
     * Define o valor total do produto.
     *
     * @param int $nItem
     * @param float $valorTotal
     * @return self
     */
    public function setvProdProduto(int $nItem, float $valorTotal): self
    {
        $this->produtos[$nItem]['vprod'] = $valorTotal;
        return $this;
    }

    /**
     * Define o EAN tributável do produto.
     *
     * @param int $nItem
     * @param string $eanTrib
     * @return self
     */
    public function setcEANTribProduto(int $nItem, string $eanTrib): self
    {
        $this->produtos[$nItem]['ceantrib'] = $eanTrib;
        return $this;
    }

    /**
     * Define a unidade tributável do produto.
     *
     * @param int $nItem
     * @param string $unidadeTributavel
     * @return self
     */
    public function setuTribProduto(int $nItem, string $unidadeTributavel): self
    {
        $this->produtos[$nItem]['utrib'] = $unidadeTributavel;
        return $this;
    }

    /**
     * Define a quantidade tributável do produto.
     *
     * @param int $nItem
     * @param float $quantidadeTributavel
     * @return self
     */
    public function setqTribProduto(int $nItem, float $quantidadeTributavel): self
    {
        $this->produtos[$nItem]['qtrib'] = $quantidadeTributavel;
        return $this;
    }

    /**
     * Define o valor unitário tributável do produto.
     *
     * @param int $nItem
     * @param float $valorUnitarioTrib
     * @return self
     */
    public function setvUnTribProduto(int $nItem, float $valorUnitarioTrib): self
    {
        $this->produtos[$nItem]['vuntrib'] = $valorUnitarioTrib;
        return $this;
    }

    /**
     * Define o indicador de total do produto.
     *
     * @param int $nItem
     * @param int $indicadorTotal
     * @return self
     */
    public function setindTotProduto(int $nItem, int $indicadorTotal): self
    {
        $this->produtos[$nItem]['indtot'] = $indicadorTotal;
        return $this;
    }

    /**
     * Define o valor total de tributos do produto.
     *
     * @param int $nItem
     * @param float $valorTotalTrib
     * @return self
     */
    public function setvTotTribProduto(int $nItem, float $valorTotalTrib): self
    {
        $this->produtos[$nItem]['vtottrib'] = $valorTotalTrib;
        return $this;
    }

    /**
     * Define a origem do produto.
     *
     * @param int $nItem
     * @param int $origem
     * @return self
     */
    public function setorigProduto(int $nItem, int $origem): self
    {
        $this->produtos[$nItem]['orig'] = $origem;
        return $this;
    }

    /**
     * Define o CSOSN do produto.
     *
     * @param int $nItem
     * @param int $csosn
     * @return self
     */
    public function setCSOSNProduto(int $nItem, int $csosn): self
    {
        $this->produtos[$nItem]['csosn'] = $csosn;
        return $this;
    }

    /**
     * Define o percentual de crédito do produto.
     *
     * @param int $nItem
     * @param float $percentualCredito
     * @return self
     */
    public function setpCredSNProduto(int $nItem, float $percentualCredito): self
    {
        $this->produtos[$nItem]['pcredsn'] = $percentualCredito;
        return $this;
    }

    /**
     * Define o valor do crédito ICMS do produto.
     *
     * @param int $nItem
     * @param float $valorCredito
     * @return self
     */
    public function setvCredICMSSNProduto(int $nItem, float $valorCredito): self
    {
        $this->produtos[$nItem]['vcredicmssn'] = $valorCredito;
        return $this;
    }

    /**
     * Define a classe de enquadramento do produto.
     *
     * @param int $nItem
     * @param int $clEnq
     * @return self
     */
    public function setclEnqProduto(int $nItem, int $clEnq): self
    {
        $this->produtos[$nItem]['clenq'] = $clEnq;
        return $this;
    }

    /**
     * Define o CNPJ do produtor do produto.
     *
     * @param int $nItem
     * @param int $cnpj
     * @return self
     */
    public function setCNPJProdProduto(int $nItem, int $cnpj): self
    {
        $this->produtos[$nItem]['cnpjprod'] = $cnpj;
        return $this;
    }

    /**
     * Define o código de enquadramento do produto.
     *
     * @param int $nItem
     * @param int $cEnq
     * @return self
     */
    public function setcEnqProduto(int $nItem, int $cEnq): self
    {
        $this->produtos[$nItem]['cenq'] = $cEnq;
        return $this;
    }

    /**
     * Define o CST 1 do produto.
     *
     * @param int $nItem
     * @param int $cst1
     * @return self
     */
    public function setCST1Produto(int $nItem, int $cst1): self
    {
        $this->produtos[$nItem]['cst_1'] = $cst1;
        return $this;
    }

    /**
     * Define o CST 2 do produto.
     *
     * @param int $nItem
     * @param int $cst2
     * @return self
     */
    public function setCST2Produto(int $nItem, int $cst2): self
    {
        $this->produtos[$nItem]['cst_2'] = $cst2;
        return $this;
    }

    /**
     * Define o CST 3 do produto.
     *
     * @param int $nItem
     * @param int $cst3
     * @return self
     */
    public function setCST3Produto(int $nItem, int $cst3): self
    {
        $this->produtos[$nItem]['cst_3'] = $cst3;
        return $this;
    }

    /**
     * Define o valor de desconto do produto.
     *
     * @param int $nItem
     * @param float $valorDesconto
     * @return self
     */
    public function setvDescProduto(int $nItem, float $valorDesconto): self
    {
        $this->produtos[$nItem]['vdesc'] = $valorDesconto;
        return $this;
    }

    /**
     * Define o ID da transportadora.
     *
     * @param int $index
     * @param int $id
     * @return self
     */
    public function setIdNfeTransportadora(int $index, int $id): self
    {
        if (!isset($this->transportadoras[$index])) {
            $this->transportadoras[$index] = [];
        }
        Log::channel('nfe')->debug("Tentativa de definir ID da transportadora ignorada: $id");
        return $this;
    }

    /**
     * Define o ID da NFe da transportadora.
     *
     * @param int $index
     * @param int $idNfe
     * @return self
     */
    public function setIdNfeNfeTransportadora(int $index, int $idNfe): self
    {
        if (!isset($this->transportadoras[$index])) {
            $this->transportadoras[$index] = [];
        }
        $this->transportadoras[$index]['id_nfe'] = $idNfe;
        return $this;
    }

    /**
     * Define a data de inclusão da transportadora.
     *
     * @param int $index
     * @param string $dataInclusao
     * @return self
     */
    public function setDataInclusaoNfeTransportadora(int $index, string $dataInclusao): self
    {
        if (!isset($this->transportadoras[$index])) {
            $this->transportadoras[$index] = [];
        }
        $this->transportadoras[$index]['data_inclusao'] = $dataInclusao;
        return $this;
    }

    /**
     * Define a data de alteração da transportadora.
     *
     * @param int $index
     * @param string $dataAlteracao
     * @return self
     */
    public function setDataAlteracaoNfeTransportadora(int $index, string $dataAlteracao): self
    {
        if (!isset($this->transportadoras[$index])) {
            $this->transportadoras[$index] = [];
        }
        $this->transportadoras[$index]['data_alteracao'] = $dataAlteracao;
        return $this;
    }

    /**
     * Define a modalidade de frete da transportadora.
     *
     * @param int $index
     * @param int $modalidade
     * @return self
     */
    public function setModFreteNfeTransportadora(int $index, int $modalidade): self
    {
        if (!isset($this->transportadoras[$index])) {
            $this->transportadoras[$index] = [];
        }
        $this->transportadoras[$index]['modfrete'] = $modalidade;
        return $this;
    }

    /**
     * Define o nome da transportadora.
     *
     * @param int $index
     * @param string $nome
     * @return self
     */
    public function setXNomeNfeTransportadora(int $index, string $nome): self
    {
        if (!isset($this->transportadoras[$index])) {
            $this->transportadoras[$index] = [];
        }
        $this->transportadoras[$index]['xnome'] = $nome;
        return $this;
    }

    /**
     * Define o endereço da transportadora.
     *
     * @param int $index
     * @param string $endereco
     * @return self
     */
    public function setXEnderNfeTransportadora(int $index, string $endereco): self
    {
        if (!isset($this->transportadoras[$index])) {
            $this->transportadoras[$index] = [];
        }
        $this->transportadoras[$index]['xender'] = $endereco;
        return $this;
    }

    /**
     * Define o município da transportadora.
     *
     * @param int $index
     * @param string $municipio
     * @return self
     */
    public function setXMunNfeTransportadora(int $index, string $municipio): self
    {
        if (!isset($this->transportadoras[$index])) {
            $this->transportadoras[$index] = [];
        }
        $this->transportadoras[$index]['xmun'] = $municipio;
        return $this;
    }

    /**
     * Define a UF da transportadora.
     *
     * @param int $index
     * @param string $uf
     * @return self
     */
    public function setUFNfeTransportadora(int $index, string $uf): self
    {
        if (!isset($this->transportadoras[$index])) {
            $this->transportadoras[$index] = [];
        }
        $this->transportadoras[$index]['uf'] = $uf;
        return $this;
    }

    /**
     * Define a quantidade de volumes da transportadora.
     *
     * @param int $index
     * @param int $quantidade
     * @return self
     */
    public function setQVolNfeTransportadora(int $index, int $quantidade): self
    {
        if (!isset($this->transportadoras[$index])) {
            $this->transportadoras[$index] = [];
        }
        $this->transportadoras[$index]['qvol'] = $quantidade;
        return $this;
    }

    /**
     * Define a marca dos volumes da transportadora.
     *
     * @param int $index
     * @param string $marca
     * @return self
     */
    public function setMarcaNfeTransportadora(int $index, string $marca): self
    {
        if (!isset($this->transportadoras[$index])) {
            $this->transportadoras[$index] = [];
        }
        $this->transportadoras[$index]['marca'] = $marca;
        return $this;
    }

    /**
     * Define a numeração dos volumes da transportadora.
     *
     * @param int $index
     * @param string $numeracao
     * @return self
     */
    public function setNVolNfeTransportadora(int $index, string $numeracao): self
    {
        if (!isset($this->transportadoras[$index])) {
            $this->transportadoras[$index] = [];
        }
        $this->transportadoras[$index]['nvol'] = $numeracao;
        return $this;
    }

    /**
     * Define o peso líquido da transportadora.
     *
     * @param int $index
     * @param float $pesoLiquido
     * @return self
     */
    public function setPesoLNfeTransportadora(int $index, float $pesoLiquido): self
    {
        if (!isset($this->transportadoras[$index])) {
            $this->transportadoras[$index] = [];
        }
        $this->transportadoras[$index]['pesol'] = $pesoLiquido;
        return $this;
    }

    /**
     * Define o peso bruto da transportadora.
     *
     * @param int $index
     * @param float $pesoBruto
     * @return self
     */
    public function setPesoBNfeTransportadora(int $index, float $pesoBruto): self
    {
        if (!isset($this->transportadoras[$index])) {
            $this->transportadoras[$index] = [];
        }
        $this->transportadoras[$index]['pesob'] = $pesoBruto;
        return $this;
    }

    /**
     * Define o ID da fatura.
     *
     * @param int $index
     * @param int $id
     * @return self
     */
    public function setidNfeFatura(int $index, int $id): self
    {
        if (!isset($this->faturas[$index])) {
            $this->faturas[$index] = [];
        }
        Log::channel('nfe')->debug("Tentativa de definir ID da fatura ignorada: $id");
        return $this;
    }

    /**
     * Define o ID da NFe da fatura.
     *
     * @param int $index
     * @param int $idNfe
     * @return self
     */
    public function setidNfeNfeFatura(int $index, int $idNfe): self
    {
        if (!isset($this->faturas[$index])) {
            $this->faturas[$index] = [];
        }
        $this->faturas[$index]['id_nfe'] = $idNfe;
        return $this;
    }

    /**
     * Define a data de inclusão da fatura.
     *
     * @param int $index
     * @param float $dataInclusao
     * @return self
     */
    public function setdataInclusaoNfeFatura(int $index, float $dataInclusao): self
    {
        if (!isset($this->faturas[$index])) {
            $this->faturas[$index] = [];
        }
        $this->faturas[$index]['data_inclusao'] = $dataInclusao;
        return $this;
    }

    /**
     * Define a data de alteração da fatura.
     *
     * @param int $index
     * @param float $dataAlteracao
     * @return self
     */
    public function setdataAlteracaoNfeFatura(int $index, float $dataAlteracao): self
    {
        if (!isset($this->faturas[$index])) {
            $this->faturas[$index] = [];
        }
        $this->faturas[$index]['data_alteracao'] = $dataAlteracao;
        return $this;
    }

    /**
     * Define o número da fatura.
     *
     * @param int $index
     * @param int $numero
     * @return self
     */
    public function setnFatNfeFatura(int $index, int $numero): self
    {
        if (!isset($this->faturas[$index])) {
            $this->faturas[$index] = [];
        }
        $this->faturas[$index]['nfat'] = $numero;
        return $this;
    }

    /**
     * Define o valor original da fatura.
     *
     * @param int $index
     * @param float $valorOriginal
     * @return self
     */
    public function setvOrigNfeFatura(int $index, float $valorOriginal): self
    {
        if (!isset($this->faturas[$index])) {
            $this->faturas[$index] = [];
        }
        $this->faturas[$index]['vorig'] = $valorOriginal;
        return $this;
    }

    /**
     * Define o valor líquido da fatura.
     *
     * @param int $index
     * @param float $valorLiquido
     * @return self
     */
    public function setvLiqNfeFatura(int $index, float $valorLiquido): self
    {
        if (!isset($this->faturas[$index])) {
            $this->faturas[$index] = [];
        }
        $this->faturas[$index]['vliq'] = $valorLiquido;
        return $this;
    }

    /**
     * Define o número da duplicata.
     *
     * @param int $index
     * @param float $numeroDuplicata
     * @return self
     */
    public function setnDupNfeFatura(int $index, float $numeroDuplicata): self
    {
        if (!isset($this->faturas[$index])) {
            $this->faturas[$index] = [];
        }
        $this->faturas[$index]['ndup'] = $numeroDuplicata;
        return $this;
    }

    /**
     * Define a data de vencimento da duplicata.
     *
     * @param int $index
     * @param string $dataVencimento
     * @return self
     */
    public function setdVencNfeFatura(int $index, string $dataVencimento): self
    {
        if (!isset($this->faturas[$index])) {
            $this->faturas[$index] = [];
        }
        $this->faturas[$index]['dvenc'] = $dataVencimento;
        return $this;
    }

    /**
     * Define o valor da duplicata.
     *
     * @param int $index
     * @param float $valorDuplicata
     * @return self
     */
    public function setvDupNfeFatura(int $index, float $valorDuplicata): self
    {
        if (!isset($this->faturas[$index])) {
            $this->faturas[$index] = [];
        }
        $this->faturas[$index]['vdup'] = $valorDuplicata;
        return $this;
    }
}
