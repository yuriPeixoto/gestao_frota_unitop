<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class PneuApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            // Dados principais do pneu
            'id_pneu' => $this->id_pneu,
            'id_filial' => $this->id_filial,
            'id_modelo_pneu' => $this->id_modelo_pneu,
            'id_controle_vida_pneu' => $this->id_controle_vida_pneu,
            'status_pneu' => $this->status_pneu,
            'data_inclusao' => $this->data_inclusao,
            'data_alteracao' => $this->data_alteracao,

            // Modelo do pneu
            'modelo' => $this->when($this->relationLoaded('modeloPneu'), function () {
                return [
                    'id_modelo_pneu' => $this->modeloPneu->id_modelo_pneu ?? null,
                    'descricao_modelo' => trim($this->modeloPneu->descricao_modelo ?? 'Modelo não cadastrado'),
                    'id_fornecedor' => $this->modeloPneu->id_fornecedor ?? null,
                    'id_dimensao_pneu_m' => $this->modeloPneu->id_dimensao_pneu_m ?? null,
                ];
            }),

            // Controle de vida do pneu
            'controleVidaPneu' => $this->when($this->relationLoaded('controleVidaPneus'), function () {
                return [
                    'id_controle_vida_pneu' => $this->controleVidaPneus->id_controle_vida_pneu ?? null,
                    'descricao_vida_pneu' => $this->controleVidaPneus->descricao_vida_pneu ?? null,
                    'km_rodagem' => $this->controleVidaPneus->km_rodagem ?? null,
                    'limite_km_rodizio' => $this->controleVidaPneus->limite_km_rodizio ?? null,
                    'sulco_pneu_novo' => $this->controleVidaPneus->sulco_pneu_novo ?? null,
                    'sulco_pneu_reformado' => $this->controleVidaPneus->sulco_pneu_reformado ?? null,
                    'id_desenho_pneu_m' => $this->controleVidaPneus->id_desenho_pneu_m ?? null,
                ];
            }),

            // Tipo/desenho do pneu
            'tipoDesenhoPneu' => $this->when($this->relationLoaded('tipoDesenhoPneu'), function () {
                return [
                    'id_desenho_pneu' => $this->tipoDesenhoPneu->id_desenho_pneu ?? null,
                    'descricao_desenho_pneu' => $this->tipoDesenhoPneu->descricao_desenho_pneu ?? null,
                    'numero_sulcos' => $this->tipoDesenhoPneu->numero_sulcos ?? null,
                    'quantidade_lona_pneu' => $this->tipoDesenhoPneu->quantidade_lona_pneu ?? null,
                    'dias_calibragem' => $this->tipoDesenhoPneu->dias_calibragem ?? null,
                ];
            }),

            // Dimensão do pneu (nested dentro do modelo)
            'dimensao_pneu' => $this->when(
                $this->relationLoaded('modeloPneu') &&
                    $this->modeloPneu &&
                    $this->modeloPneu->relationLoaded('dimensao_pneu'),
                function () {
                    return [
                        'descricao_pneu' => $this->modeloPneu->dimensaoPneu->descricao_pneu ?? null,
                    ];
                }
            ),
        ];
    }
}