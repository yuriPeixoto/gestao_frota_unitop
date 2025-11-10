<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VEstoqueCombustivel;
use App\Models\VFilial;
use App\Traits\ExportableTrait;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EstoqueCombustivelController extends Controller
{
    use ExportableTrait;

    /**
     * Display the dashboard for fuel inventory.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();

        // Obter parâmetros de filtro
        $dataInicio = $request->input('data_inicio');
        $dataFim = $request->input('data_fim');
        $dataEspecifica = $request->input('data_especifica');
        $tipoMovimentacao = $request->input('tipo_movimentacao');
        $filtroFilial = $request->input('filial_id');
        $orderBy = $request->input('order_by', 'data_alteracao');
        $orderDirection = $request->input('order_direction', 'desc');

        // Determinar filial baseado no acesso do usuário
        $filialId = $this->determineAccessibleFilialId($user, $filtroFilial);

        // Cache key com base nos filtros e acesso do usuário
        $accessibleFilials = implode(',', $user->getAccessibleFilialIds());
        $cacheKey = "estoque_combustivel_dashboard_{$user->id}_{$accessibleFilials}_{$filialId}_{$dataInicio}_{$dataFim}_{$dataEspecifica}_{$tipoMovimentacao}_{$orderBy}_{$orderDirection}";

        // Adicionar esta chave de cache à lista para poder limpá-la mais tarde
        $cacheKeys = Cache::get('estoque_combustivel_dashboard_keys', []);
        if (! in_array($cacheKey, $cacheKeys)) {
            $cacheKeys[] = $cacheKey;
            Cache::put('estoque_combustivel_dashboard_keys', $cacheKeys, 86400); // 24 horas
        }

        // Cache os dados por 15 minutos
        $tanks = Cache::remember($cacheKey, 900, function () use ($user, $filialId, $dataInicio, $dataFim, $dataEspecifica, $tipoMovimentacao) {
            return $this->getTanksData($user, $filialId, $dataInicio, $dataFim, $dataEspecifica, $tipoMovimentacao);
        });

        // Obter dados históricos se solicitado
        $movimentacoes = null;
        if ($dataInicio && $dataFim || $dataEspecifica) {
            $query = $this->buildMovimentacoesQuery($dataInicio, $dataFim, $dataEspecifica, $user, $filialId, $tipoMovimentacao);
            $movimentacoes = $query->orderBy($orderBy, $orderDirection)->get();
            Log::info('Movimentações obtidas', ['count' => count($movimentacoes)]);
        }

        // Obter lista de filiais acessíveis para o filtro
        $filiais = $this->getAccessibleFiliais($user);

        return view('admin.estoque-combustivel.dashboard', [
            'tanks' => $tanks,
            'movimentacoes' => $movimentacoes,
            'filtros' => [
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim,
                'data_especifica' => $dataEspecifica,
                'tipo_movimentacao' => $tipoMovimentacao,
                'filial_id' => $filialId,
                'order_by' => $orderBy,
                'order_direction' => $orderDirection,
            ],
            'filiais' => $filiais,
        ]);
    }

    /**
     * Determina qual filial ID usar baseado no acesso do usuário
     *
     * @param  \App\Models\User  $user
     * @param  int|null  $filtroFilial
     * @return int|null
     */
    private function determineAccessibleFilialId($user, $filtroFilial = null)
    {
        // Se usuário da MATRIZ, pode filtrar por qualquer filial ou ver todas
        if ($user->isMatriz()) {
            return $filtroFilial; // null = todas, específico = filtrar
        }

        // Se usuário de outras filiais
        $accessibleFilials = $user->getAccessibleFilialIds();

        // Se filtro foi especificado, verificar se tem acesso
        if ($filtroFilial && in_array($filtroFilial, $accessibleFilials)) {
            return $filtroFilial;
        }

        // Se não especificou filtro ou não tem acesso, retornar null para mostrar todas suas filiais
        return null;
    }

    /**
     * Obter a lista de filiais acessíveis para o usuário
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Support\Collection
     */
    private function getAccessibleFiliais($user)
    {
        $cacheKey = "filiais_acessiveis_{$user->id}";

        return Cache::remember($cacheKey, 86400, function () use ($user) {
            if ($user->isMatriz()) {
                // Usuário da MATRIZ vê todas as filiais - USANDO MODEL COM CONEXÃO DE PRODUÇÃO
                return VFilial::select('id', 'name')
                    ->orderBy('name')
                    ->get();
            } else {
                // Usuário de outras filiais vê apenas suas filiais acessíveis - USANDO MODEL COM CONEXÃO DE PRODUÇÃO
                $accessibleFilialIds = $user->getAccessibleFilialIds();

                return VFilial::select('id', 'name')
                    ->whereIn('id', $accessibleFilialIds)
                    ->orderBy('name')
                    ->get();
            }
        });
    }

    /**
     * Get all the tank data organized by location with filial access control.
     *
     * @param  \App\Models\User  $user
     * @param  int|null  $filialId
     * @param  string|null  $dataInicio
     * @param  string|null  $dataFim
     * @param  string|null  $dataEspecifica
     * @param  string|null  $tipoMovimentacao
     * @return array
     */
    private function getTanksData($user, $filialId = null, $dataInicio = null, $dataFim = null, $dataEspecifica = null, $tipoMovimentacao = null)
    {
        // Obter tanques com filtro de acesso baseado no usuário
        $query = VEstoqueCombustivel::query();

        // Aplicar filtro de filiais acessíveis
        if (! $user->isMatriz()) {
            // Usuário não é da MATRIZ - filtrar apenas filiais acessíveis
            $accessibleFilialIds = $user->getAccessibleFilialIds();

            // Mapear IDs de filiais para nomes usando model com conexão de produção
            $filialNomes = VFilial::whereIn('id', $accessibleFilialIds)
                ->pluck('name')
                ->toArray();

            Log::info('Filtrando tanques para filiais acessíveis', [
                'user_id' => $user->id,
                'accessible_filial_ids' => $accessibleFilialIds,
                'accessible_filial_names' => $filialNomes,
            ]);

            $query->whereIn('nome_filial', $filialNomes);
        }

        // Filtrar por filial específica se solicitado
        if ($filialId) {
            // Buscar nome da filial usando model com conexão de produção
            $filialNome = VFilial::where('id', $filialId)->value('name');

            if ($filialNome) {
                Log::info("Filtrando por filial específica: {$filialNome} (ID: {$filialId})");
                $query->where('nome_filial', $filialNome);
            } else {
                Log::warning("Filial ID {$filialId} não encontrada");
            }
        }

        $allTanks = $query->get();
        Log::info('Tanques encontrados: ' . $allTanks->count());

        // Tank IDs que queremos exibir no dashboard
        $tankIds = [1, 2, 3, 152, 131, 4, 166, 6, 5];

        // Organizar tanques dinamicamente por filial
        $locations = [];

        foreach ($allTanks as $tank) {
            if (! in_array($tank->id_tanque, $tankIds)) {
                continue;
            }

            $filialName = $tank->nome_filial;
            $locationKey = $this->getLocationKey($filialName);

            // Se não temos uma chave de localização, criar uma baseada no nome da filial
            if (! $locationKey) {
                $locationKey = strtolower(str_replace(' ', '_', $filialName));
            }

            // Inicializar localização se não existir
            if (! isset($locations[$locationKey])) {
                $locations[$locationKey] = [
                    'name' => $filialName,
                    'icon_color' => $this->getFilialIconColor($filialName),
                    'tanks' => [],
                ];
            }

            $locations[$locationKey]['tanks'][] = [
                'id' => $tank->id_tanque,
                'name' => $tank->tanque,
                'fuel_type' => $tank->tipo_combustivel,
                'current_amount' => $tank->quantidade_em_estoque,
                'capacity' => $tank->capacidade_tanque,
                'percentage' => $tank->getPercentage(),
                'last_updated' => $tank->data_alteracao,
                'display_name' => strtolower($tank->tanque), // Para propósitos de exibição
            ];
        }

        // Ordenar tanques dentro de cada localização
        $this->sortTanksInLocations($locations);

        Log::info('Localizações organizadas', [
            'user_id' => $user->id,
            'locations' => array_keys($locations),
            'tank_count_by_location' => array_map(function ($loc) {
                return count($loc['tanks']);
            }, $locations),
        ]);

        return $locations;
    }

    /**
     * Get filial icon color based on filial name.
     *
     * @param  string  $filialName
     * @return string
     */
    private function getFilialIconColor($filialName)
    {
        // Cores alternadas para diferentes filiais
        $colorMap = [
            'Matriz' => '#0984E3',
            'Campo Grande' => '#2980B9',
            'São Paulo' => '#0984E3',
            'Sinop' => '#2980B9',
            'Cuiabá' => '#0984E3',
            'Dourados' => '#2980B9',
            'Vilhena' => '#0984E3',
            'Curitiba' => '#2980B9',
            'Rondonópolis' => '#0984E3',
            'Navegantes' => '#2980B9',
            'Joinville' => '#0984E3',
        ];

        return $colorMap[$filialName] ?? '#6B7280'; // Cor padrão (cinza) se não encontrar
    }

    /**
     * Constrói a query para buscar movimentações com controle de acesso por filial.
     *
     * @param  string|null  $dataInicio
     * @param  string|null  $dataFim
     * @param  string|null  $dataEspecifica
     * @param  \App\Models\User  $user
     * @param  int|null  $filialId
     * @param  string|null  $tipoMovimentacao
     * @return \Illuminate\Database\Query\Builder
     */
    private function buildMovimentacoesQuery($dataInicio = null, $dataFim = null, $dataEspecifica = null, $user = null, $filialId = null, $tipoMovimentacao = null)
    {
        // *** CORREÇÃO PRINCIPAL: USAR CONEXÃO DE PRODUÇÃO ***
        $query = DB::connection('pgsql')
            ->table('estoque_combustivel as e')
            ->join('tanque as t', 't.id_tanque', '=', 'e.id_tanque')
            ->join('tipocombustivel as tc', 'tc.id_tipo_combustivel', '=', 't.combustivel')
            ->join('filiais as vf', 'vf.id', '=', 't.id_filial')
            ->select(
                'e.id_estoque_combustivel',
                'e.data_inclusao',
                'e.data_alteracao',
                't.tanque',
                'tc.descricao as tipo_combustivel',
                'vf.name as nome_filial',
                'vf.id as filial_id',
                'e.quantidade_em_estoque',
                'e.quantidade_anterior',
                DB::raw('(e.quantidade_em_estoque - COALESCE(e.quantidade_anterior, 0)) as diferenca'),
                't.capacidade as capacidade_tanque'
            );

        // Aplicar controle de acesso por filial
        if ($user && ! $user->isMatriz()) {
            $accessibleFilialIds = $user->getAccessibleFilialIds();
            Log::info('Filtrando movimentações por filiais acessíveis', [
                'user_id' => $user->id,
                'accessible_filials' => $accessibleFilialIds,
            ]);
            $query->whereIn('t.id_filial', $accessibleFilialIds);
        }

        // Filtrar por data específica ou intervalo
        if ($dataEspecifica) {
            $query->whereRaw('e.data_alteracao::date = ?', [$dataEspecifica]);
        } elseif ($dataInicio && $dataFim) {
            $query->whereBetween('e.data_alteracao', [$dataInicio . ' 00:00:00', $dataFim . ' 23:59:59']);
        }

        // Filtrar por filial específica se solicitado e usuário tem acesso
        if ($filialId) {
            if (! $user || $user->isMatriz() || $user->hasAccessToFilial($filialId)) {
                Log::info("Filtrando movimentações por filial específica: {$filialId}");
                $query->where('t.id_filial', $filialId);
            } else {
                Log::warning('Usuário tentou acessar filial sem permissão', [
                    'user_id' => $user->id,
                    'requested_filial' => $filialId,
                    'accessible_filials' => $user->getAccessibleFilialIds(),
                ]);
            }
        }

        // Filtrar por tipo de movimentação (entrada ou saída)
        if ($tipoMovimentacao) {
            if ($tipoMovimentacao == 'entrada') {
                $query->whereRaw('(e.quantidade_em_estoque - COALESCE(e.quantidade_anterior, 0)) > 0');
            } elseif ($tipoMovimentacao == 'saida') {
                $query->whereRaw('(e.quantidade_em_estoque - COALESCE(e.quantidade_anterior, 0)) < 0');
            }
        }

        // Tank IDs que queremos exibir no dashboard
        $tankIds = [1, 2, 3, 152, 131, 4, 166, 6, 5];
        $query->whereIn('t.id_tanque', $tankIds);

        return $query;
    }

    /**
     * Get the location key based on the location name.
     *
     * @param  string  $locationName
     * @return string|null
     */
    private function getLocationKey($locationName)
    {
        // Mapeamento para manter compatibilidade com localizações conhecidas
        $locationMap = [
            'Matriz' => 'matriz',
            'Campo Grande' => 'campo_grande',
            'São Paulo' => 'sao_paulo',
            'Sinop' => 'sinop',
        ];

        return $locationMap[$locationName] ?? null;
    }

    /**
     * Sort tanks within each location based on specific order.
     * *** CORRIGIDO PARA PSR-12 ***
     *
     * @param  array  $locations
     * @return void
     */
    private function sortTanksInLocations(&$locations)
    {
        // Define the order of tanks for each location
        $orderMap = [
            'matriz' => [1, 2, 3], // S500, S10, Arla
            'campo_grande' => [152, 131], // S10, Arla
            'sao_paulo' => [4, 166], // S10, Arla
            'sinop' => [6, 5], // TD90, TA90 (Sinop real)
        ];

        foreach ($locations as $key => &$location) {
            if (isset($orderMap[$key])) {
                $order = $orderMap[$key];
                usort($location['tanks'], function ($a, $b) use ($order) {
                    $posA = array_search($a['id'], $order);
                    $posB = array_search($b['id'], $order);

                    // *** CORRIGIDO: PSR-12 COMPLIANT ***
                    // Se um dos tanques não está na ordem definida, colocar no final
                    if ($posA === false) {
                        $posA = 999;
                    }

                    if ($posB === false) {
                        $posB = 999;
                    }

                    return $posA - $posB;
                });
            } else {
                // Para filiais não mapeadas, ordenar por ID do tanque
                usort($location['tanks'], function ($a, $b) {
                    return $a['id'] - $b['id'];
                });
            }
        }
    }

    /**
     * Refresh the dashboard data (clear cache and reload).
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function refreshData()
    {
        // Limpar todos os caches relacionados ao dashboard
        $cacheKeys = Cache::get('estoque_combustivel_dashboard_keys', []);
        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        // Limpar também caches de filiais acessíveis
        $users = \App\Models\User::select('id')->get();
        foreach ($users as $user) {
            Cache::forget("filiais_acessiveis_{$user->id}");
            Cache::forget("user_accessible_filials_{$user->id}");
        }

        Cache::forget('estoque_combustivel_dashboard');
        Cache::forget('estoque_combustivel_dashboard_keys');
        Cache::forget('filiais_para_filtro');

        return redirect()->route('admin.estoque-combustivel.dashboard')->with('success', 'Dados atualizados com sucesso!');
    }

    /**
     * Lista de filtros válidos para exportação
     *
     * @return array
     */
    protected function getValidExportFilters()
    {
        return [
            'data_inicio',
            'data_fim',
            'data_especifica',
            'tipo_movimentacao',
            'filial_id',
            'order_by',
            'order_direction',
        ];
    }

    /**
     * Construir a query para exportação com controle de acesso
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function buildExportQuery(Request $request)
    {
        $user = Auth::user();
        $filialId = $this->determineAccessibleFilialId($user, $request->input('filial_id'));

        return $this->buildMovimentacoesQuery(
            $request->input('data_inicio'),
            $request->input('data_fim'),
            $request->input('data_especifica'),
            $user,
            $filialId,
            $request->input('tipo_movimentacao')
        )->orderBy(
            $request->input('order_by', 'e.data_alteracao'),
            $request->input('order_direction', 'desc')
        );
    }

    /**
     * Verificar se a exportação excede o limite recomendado (sobrescrito para Query Builder)
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     */
    protected function exceedsExportLimit($query, int $limit = 1000): bool
    {
        // Contar apenas as linhas, não todos os dados
        $count = $query->count();

        return $count > $limit;
    }

    /**
     * Exportar para PDF
     *
     * @return \Illuminate\Http\Response
     */
    public function exportPdf(Request $request)
    {
        $query = $this->buildExportQuery($request);

        if (! $this->hasAnyFilter($request, $this->getValidExportFilters())) {
            return redirect()->back()->with([
                'error' => 'É necessário aplicar pelo menos um filtro antes de exportar.',
                'export_error' => true,
            ]);
        }

        if ($request->has('confirmed') || ! $this->exceedsExportLimit($query, 500)) {
            $data = $query->get();

            // Configurar opções do PDF
            $pdf = app('dompdf.wrapper');
            $pdf->setPaper('a4', 'landscape');

            // Carregar a view
            $pdf->loadView('admin.estoque-combustivel.pdf', compact('data'));

            // Forçar download em vez de exibir no navegador
            return $pdf->download('estoque_combustivel_' . date('Y-m-d_His') . '.pdf');
        } else {
            // Confirmação para grande volume
            $currentUrl = $request->fullUrlWithQuery(['confirmed' => 1]);

            return redirect()->back()->with([
                'warning' => 'Você está tentando exportar mais de 500 registros, o que pode levar mais tempo.',
                'export_confirmation' => true,
                'export_url' => $currentUrl,
            ]);
        }
    }

    /**
     * Verificar se algum filtro foi aplicado na requisição (sobrescrito para compatibilidade)
     *
     * @param  array  $allowedFilters  Lista de parâmetros considerados filtros válidos
     * @return bool
     */
    protected function hasAnyFilter(Request $request, array $allowedFilters = [])
    {
        // Se não foram especificados filtros permitidos, considera todos os parâmetros exceto alguns padrão
        if (empty($allowedFilters)) {
            $params = $request->except(['_token', 'page', 'perPage', 'sort', 'order', 'confirmed']);
        } else {
            $params = $request->only($allowedFilters);
        }

        // Verifica se há algum filtro preenchido
        foreach ($params as $key => $value) {
            if (! empty($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Exportar para Excel
     *
     * @return \Illuminate\Http\Response
     */
    public function exportXls(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'data_alteracao' => 'Data',
            'nome_filial' => 'Filial',
            'tanque' => 'Tanque',
            'tipo_combustivel' => 'Combustível',
            'quantidade_anterior' => 'Quantidade Anterior (L)',
            'quantidade_em_estoque' => 'Quantidade Atual (L)',
            'diferenca' => 'Diferença (L)',
        ];

        // Usar o método personalizado em vez do trait
        return $this->customExportToExcel(
            $request,
            $query,
            $columns,
            'estoque_combustivel',
            $this->getValidExportFilters()
        );
    }

    /**
     * Exportar para CSV
     *
     * @return \Illuminate\Http\Response
     */
    public function exportCsv(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'data_alteracao' => 'Data',
            'nome_filial' => 'Filial',
            'tanque' => 'Tanque',
            'tipo_combustivel' => 'Combustível',
            'quantidade_anterior' => 'Quantidade Anterior (L)',
            'quantidade_em_estoque' => 'Quantidade Atual (L)',
            'diferenca' => 'Diferença (L)',
        ];

        // Usar o método personalizado em vez do trait
        return $this->customExportToCsv(
            $request,
            $query,
            $columns,
            'estoque_combustivel',
            $this->getValidExportFilters()
        );
    }

    /**
     * Versão personalizada do método exportToExcel para trabalhar com Query Builder
     *
     * @return mixed
     */
    protected function customExportToExcel(
        Request $request,
        Builder $query,
        array $columns,
        string $filename,
        array $allowedFilters = [],
        int $limit = 1000
    ) {
        // Verificar se algum filtro foi aplicado
        if (! $this->hasAnyFilter($request, $allowedFilters)) {
            return back()->with([
                'error' => 'É necessário aplicar pelo menos um filtro antes de exportar.',
                'export_error' => true,
            ]);
        }

        // Verificar se foi confirmado ou se não excede o limite
        if ($request->has('confirmed') || ! $this->exceedsExportLimit($query, $limit)) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();

            // Adicionar cabeçalhos
            $col = 'A';
            foreach ($columns as $label) {
                $sheet->setCellValue($col . '1', $label);
                $col++;
            }

            // Processar todos os resultados
            $results = $query->get();
            $row = 2;

            foreach ($results as $record) {
                $col = 'A';
                foreach ($columns as $key => $label) {
                    $value = data_get($record, $key);
                    // Formatar datas se necessário
                    if ($key === 'data_alteracao') {
                        $value = \Carbon\Carbon::parse($value)->format('d/m/Y H:i');
                    }
                    $sheet->setCellValue($col . $row, $value);
                    $col++;
                }
                $row++;
            }

            // Configurar cabeçalhos para download
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $fileName = $filename . '_' . date('Y-m-d_His') . '.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $fileName . '"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;
        } else {
            // Redirecionar com alerta para confirmar exportação de grande volume
            $currentUrl = $request->fullUrlWithQuery(['confirmed' => 1]);

            return back()->with([
                'warning' => "Você está tentando exportar mais de {$limit} registros, o que pode levar mais tempo.",
                'export_confirmation' => true,
                'export_url' => $currentUrl,
            ]);
        }
    }

    /**
     * Versão personalizada do método exportToCsv para trabalhar com Query Builder
     *
     * @return mixed
     */
    protected function customExportToCsv(
        Request $request,
        Builder $query,
        array $columns,
        string $filename,
        array $allowedFilters = [],
        int $limit = 1000
    ) {
        // Verificar se algum filtro foi aplicado
        if (! $this->hasAnyFilter($request, $allowedFilters)) {
            return back()->with([
                'error' => 'É necessário aplicar pelo menos um filtro antes de exportar.',
                'export_error' => true,
            ]);
        }

        // Verificar se foi confirmado ou se não excede o limite
        if ($request->has('confirmed') || ! $this->exceedsExportLimit($query, $limit)) {
            // Configurações para o CSV
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '_' . date('Y-m-d_His') . '.csv"',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];

            // Usar streaming para evitar carregamento de todos os dados na memória
            return response()->stream(function () use ($query, $columns) {
                $handle = fopen('php://output', 'w');

                // Adicionar BOM para UTF-8
                fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

                // Escrever cabeçalhos
                fputcsv($handle, array_values($columns));

                // Processar todos os resultados
                $results = $query->get();

                foreach ($results as $record) {
                    $row = [];
                    foreach ($columns as $key => $label) {
                        $value = data_get($record, $key);
                        // Formatar datas se necessário
                        if ($key === 'data_alteracao') {
                            $value = \Carbon\Carbon::parse($value)->format('d/m/Y H:i');
                        }
                        $row[] = $value;
                    }
                    fputcsv($handle, $row);
                }

                fclose($handle);
            }, 200, $headers);
        } else {
            // Redirecionar com alerta para confirmar exportação de grande volume
            $currentUrl = $request->fullUrlWithQuery(['confirmed' => 1]);

            return back()->with([
                'warning' => "Você está tentando exportar mais de {$limit} registros, o que pode levar mais tempo.",
                'export_confirmation' => true,
                'export_url' => $currentUrl,
            ]);
        }
    }
}
