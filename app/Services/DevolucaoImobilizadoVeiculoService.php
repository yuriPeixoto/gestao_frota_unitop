<?php

namespace App\Services;

use App\Models\Departamento;
use App\Services\IntegracaoWhatssappCarvalimaService;
use App\Models\DepartamentoTransferencia;
use App\Models\Filial;
use App\Models\Fornecedor;
use App\Models\TelefoneTransferencia;
use App\Models\TipoEquipamento;
use App\Models\DevolucaoImobilizadoVeiculo;
use Illuminate\Support\Facades\Log;
use App\Services\ChecklistService;
use Illuminate\Support\Facades\Auth;


class DevolucaoImobilizadoVeiculoService
{
   private ChecklistService $checklistService;

   public function __construct(ChecklistService $checklistService)
   {
      $this->checklistService = $checklistService;
   }

   public function mensagemTransferencia(DevolucaoImobilizadoVeiculo $DevolucaoImobilizadoVeiculo, $status, $statusNome)
   {
      $departamento = $statusNome;

      $data = format_date($DevolucaoImobilizadoVeiculo->data_alteracao);

      // Busca os telefones que têm o departamento especifico ou 'ADMIN'
      $telefones = TelefoneTransferencia::select('telefone as label', 'nome as value', 'departamento')
         ->whereIn('departamento', [$status, 7])
         ->orderBy('telefone', 'desc')
         ->get()
         ->toArray();

      if (!empty($telefones)) {
         $tipoEquipamento = TipoEquipamento::find($DevolucaoImobilizadoVeiculo->id_tipo_equipamento);

         // Envia a notificação para cada telefone retornado
         foreach ($telefones as $telefoneData) {
            $telefone = $telefoneData['label'];
            $nome = $telefoneData['value'];

            if (!empty($telefone)) {
               // Texto da mensagem para o WhatsApp
               $texto = "*Atenção:* $nome. \n"
                  . "A transferência de veiculo *$DevolucaoImobilizadoVeiculo->id_devolucao_imobilizado_veiculo* foi enviada para $statusNome.\n"
                  . "Tipo Veículo: $tipoEquipamento->descricao_tipo\n"
                  . "Observação: $DevolucaoImobilizadoVeiculo->observacao\n"
                  . "Data: $data\n"
                  . "Situação: *$departamento*.\n";

               // Envia a mensagem via WhatsApp
               IntegracaoWhatssappCarvalimaService::enviarMensagem($texto, "$nome", "$telefone");
            }
         }
      }
   }

   public function situacaoImobilizado($status)
   {
      $departamentoTransferencia = DepartamentoTransferencia::where('departamento', $status)->first();
      return $departamentoTransferencia->id_departamento_transferencia;
   }

   public function getIdTipoEquipamento()
   {

      return DevolucaoImobilizadoVeiculo::join('tipoequipamento', 'transferencia_imobilizado_veiculo.id_tipo_equipamento', '=', 'tipoequipamento.id_tipo_equipamento')
         ->select('tipoequipamento.descricao_tipo as label', 'transferencia_imobilizado_veiculo.id_tipo_equipamento as value')
         ->orderBy('tipoequipamento.descricao_tipo')
         ->distinct()
         ->get()
         ->toArray();
   }

   public function getTipoEquipamento()
   {

      return TipoEquipamento::select('id_tipo_equipamento', 'descricao_tipo', 'numero_eixos')
         ->orderBy('descricao_tipo')
         ->get()
         ->map(function ($item) {
            if (!empty($item->numero_eixos)) {
               $eixo = ' - eixos: ' . $item->numero_eixos;
            }

            return [
               'value' => $item->id_tipo_equipamento,
               'label' => $item->descricao_tipo . ($eixo ?? ''),
            ];
         });
   }

   public function getFilial()
   {
      return  Filial::select('name as label', 'id as value')
         ->orderBy('name')
         ->get()
         ->toArray();
   }

   public function getDepartamento()
   {
      return  Departamento::select('descricao_departamento as label', 'id_departamento as value')
         ->orderBy('descricao_departamento')
         ->get()
         ->toArray();
   }

   public function getFornecedor()
   {
      return Fornecedor::select(
         'id_fornecedor as value',
         'nome_fornecedor as label'
      )
         ->orderBy('nome_fornecedor', 'asc')
         ->limit(30)
         ->get()
         ->toArray();
   }

   public function getSituacao()
   {
      return  DepartamentoTransferencia::select('departamento as label', 'id_departamento_transferencia as value')
         ->where('departamento', '!=', 'ADMIN')
         ->orderBy('departamento')
         ->get()
         ->toArray();
   }

   public function createChecklist($id)
   {
      try {
         $DevolucaoImobilizadoVeiculo = DevolucaoImobilizadoVeiculo::with('tipoEquipamento')->findOrFail($id);

         // DEBUG: Log da descrição completa para verificar concatenação
         $descricaoCompleta = $DevolucaoImobilizadoVeiculo->tipoEquipamento->getDescricaoCompleta();

         // Obtém o tipo de checklist da model TipoEquipamento
         $checklistTypeId = $DevolucaoImobilizadoVeiculo->tipoEquipamento->getChecklistTypeId();

         if ($DevolucaoImobilizadoVeiculo->status == 4) {
            $title = 'Checklist de Transferencia de Imobilizado Veículo';
            $idFilial = $DevolucaoImobilizadoVeiculo->id_filial_origem;
         } else {
            $title = 'Checklist de Transferencia de Imobilizado Veículo Devolutiva ';
            $idFilial = $DevolucaoImobilizadoVeiculo->id_filial_destino;
         }
         $description = $DevolucaoImobilizadoVeiculo->observacao ?? 'Nenhuma observação fornecida';
         $entityType = 'vehicle';
         $entityId = $DevolucaoImobilizadoVeiculo->id_veiculo;
         $createdBy = Auth::user()->id;
         $department_id = Auth::user()->departamento_id;
         $dueDate = $DevolucaoImobilizadoVeiculo->data_fim; // Defina a data de vencimento conforme necessário

         $data = $this->checklistService->buildChecklistData(
            checklistTypeId: $checklistTypeId,
            title: $title,
            description: $description,
            entityType: $entityType,
            entityId: $entityId,
            createdBy: $createdBy,
            department_id: $department_id,
            id_filial: $idFilial,
            dueDate: ($dueDate ?? now())->format('Y-m-d\TH:i:s')
         );

         $result = $this->checklistService->createChecklist($data);

         if (!$DevolucaoImobilizadoVeiculo->checklist_id) {
            $checklistID = $result['data']['id'];
            $DevolucaoImobilizadoVeiculo->update([
               'checklist_id' => $checklistID
            ]);

            Log::info('Checklist adicionado na transferencia corretamente' . $result['data']['id']);
         } else {
            $checklistID = $result['data']['id'];
            $DevolucaoImobilizadoVeiculo->update([
               'checklist_devo' => $checklistID
            ]);

            Log::info('Checklist adicionado na transferencia corretamente' . $result['data']['id']);
         }

         return response()->json($result, 201);
      } catch (\Exception $e) {
         Log::error('Erro ao criar checklist para transferência de imobilizado veiculo: ' . $e->getMessage(), [
            'exception' => $e
         ]);
         return response()->json(['error' => $e->getMessage()], 500);
      }
   }
}
