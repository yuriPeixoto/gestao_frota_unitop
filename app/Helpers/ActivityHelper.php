<?php

use App\Modules\Configuracoes\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

if (!function_exists('getRelevantChanges')) {
    /**
     * Filtra apenas as mudanças relevantes para o usuário final
     *
     * @param array $changes Array de mudanças (old_values ou new_values)
     * @return array Array filtrado com apenas campos relevantes
     */
    function getRelevantChanges($changes)
    {
        if (empty($changes)) {
            return [];
        }

        // Campos que devem ser completamente ocultados do usuário final (apenas os realmente sensíveis)
        $hiddenFields = [
            'password',
            'remember_token',
            'api_token',
            'two_factor_secret',
            'two_factor_recovery_codes',
            'password_reset_token',
            'updated_at', // Timestamp automático
            'created_at', // Timestamp automático
        ];

        $filtered = [];

        foreach ($changes as $key => $value) {
            // Skip apenas campos realmente sensíveis
            if (in_array($key, $hiddenFields)) {
                continue;
            }

            // Incluir todos os outros campos - deixar o usuário ver o que mudou
            $filtered[$key] = $value;
        }

        return $filtered;
    }
}

if (!function_exists('formatActivityValue')) {
    /**
     * Formata valores do log de atividades para exibição amigável ao usuário
     *
     * @param string $key Nome do campo
     * @param mixed $value Valor do campo
     * @param Collection $users Coleção de usuários para referência
     * @return string
     */
    function formatActivityValue($key, $value, $users)
    {
        if (is_null($value)) {
            return '<span class="text-gray-400">Nenhum</span>';
        }

        // Tratamento especial para campos específicos
        switch (true) {
            // IDs de usuário - buscar o nome para contexto
            case str_ends_with($key, 'user_id'):
                $user = $users[$value] ?? User::find($value);
                return $user
                    ? sprintf('<span class="text-gray-900">%s</span>',
                        e($user->name)
                    )
                    : sprintf('<span class="text-gray-500">Usuário #%d (removido)</span>', $value);

            // IDs de relacionamentos - tentar buscar informações contextuais
            case str_ends_with($key, '_id') && is_numeric($value):
                return formatRelationshipValue($key, $value);

            // Campos de data/hora
            case in_array($key, ['created_at', 'updated_at', 'deleted_at', 'email_verified_at', 'started_at', 'finished_at', 'scheduled_at', 'due_date', 'birth_date', 'hire_date']):
                return $value ? Carbon::parse($value)->format('d/m/Y H:i:s') : null;

            // Campos booleanos
            case in_array($key, ['is_superuser', 'is_active', 'is_admin', 'active', 'inactive']):
                return $value
                    ? '<span class="text-green-600">Sim</span>'
                    : '<span class="text-red-600">Não</span>';

            // Campos monetários
            case in_array($key, ['price', 'cost', 'total', 'subtotal', 'value']) && is_numeric($value):
                return sprintf('<span class="text-gray-900">R$ %s</span>', number_format($value, 2, ',', '.'));

            // Campos que devem ser ocultados (não deveria chegar aqui devido ao filtro, mas por segurança)
            case in_array($key, ['password', 'remember_token', 'ip_address', 'user_agent', 'session_id', 'api_token', 'uuid', 'hash', 'token']):
                return '<span class="text-gray-400">[oculto]</span>';

            // Arrays e objetos - simplificar exibição
            case is_array($value) || is_object($value):
                return formatArrayValue($value);

            // E-mails
            case $key === 'email' || str_ends_with($key, '_email'):
                return sprintf('<a href="mailto:%s" class="text-blue-600">%s</a>', e($value), e($value));

            // Telefones
            case in_array($key, ['phone', 'mobile', 'landline', 'fax']) || str_contains($key, 'phone') || str_contains($key, 'telefone'):
                return sprintf('<span class="text-gray-900">%s</span>', formatPhone($value));

            // CPF/CNPJ
            case in_array($key, ['cpf', 'cnpj']):
                return sprintf('<span class="text-gray-900">%s</span>', formatDocument($key, $value));

            // Demais campos de texto
            default:
                // Se for um número muito grande, provavelmente é um ID
                if (is_numeric($value) && strlen($value) > 5) {
                    return sprintf('<span class="text-gray-500">#%d</span>', $value);
                }

                // Textos longos - limitar exibição
                if (is_string($value) && strlen($value) > 100) {
                    return sprintf(
                        '<span class="text-gray-900">%s...</span> <button class="text-xs text-blue-600 ml-1" onclick="this.previousSibling.textContent = \'%s\'; this.style.display = \'none\';">ver mais</button>',
                        e(substr($value, 0, 100)),
                        e($value)
                    );
                }

                return sprintf('<span class="text-gray-900">%s</span>', e($value));
        }
    }
}

if (!function_exists('formatRelationshipValue')) {
    /**
     * Formata valores de relacionamentos (IDs) tentando buscar informações contextuais
     *
     * @param string $key Nome do campo
     * @param mixed $value Valor do ID
     * @return string
     */
    function formatRelationshipValue($key, $value)
    {
        // Mapeamento de relacionamentos comuns
        $relationshipMap = [
            'branch_id' => ['App\Models\Branch', 'name'],
            'role_id' => ['App\Models\Role', 'name'],
            'department_id' => ['App\Models\Departamento', 'name'],
            'fornecedor_id' => ['App\Models\Fornecedor', 'name'],
            'veiculo_id' => ['App\Models\Veiculo', 'placa'],
            'motorista_id' => ['App\Models\Motorista', 'name'],
            'produto_id' => ['App\Models\Produto', 'name'],
            'tipo_veiculo_id' => ['App\Models\TipoVeiculo', 'name'],
            'tipo_combustivel_id' => ['App\Models\TipoCombustivel', 'name'],
        ];

        if (isset($relationshipMap[$key])) {
            [$modelClass, $displayField] = $relationshipMap[$key];

            try {
                if (class_exists($modelClass)) {
                    $model = $modelClass::find($value);
                    if ($model && isset($model->$displayField)) {
                        return sprintf('<span class="text-gray-900">%s</span> <span class="text-xs text-gray-500">(#%d)</span>',
                            e($model->$displayField),
                            $value
                        );
                    }
                }
            } catch (Exception $e) {
                // Em caso de erro, retorna apenas o ID
            }
        }

        return sprintf('<span class="text-gray-500">#%d</span>', $value);
    }
}

if (!function_exists('formatArrayValue')) {
    /**
     * Formata arrays e objetos de forma mais amigável
     *
     * @param mixed $value Array ou objeto
     * @return string
     */
    function formatArrayValue($value)
    {
        if (empty($value)) {
            return '<span class="text-gray-400">Vazio</span>';
        }

        $count = is_array($value) ? count($value) : count((array) $value);

        // Se for um array pequeno e simples, exibir de forma resumida
        if ($count <= 3 && is_array($value)) {
            $simpleItems = [];
            foreach ($value as $k => $v) {
                if (is_scalar($v)) {
                    $simpleItems[] = is_numeric($k) ? $v : "$k: $v";
                }
            }

            if (count($simpleItems) === $count) {
                return sprintf('<span class="text-gray-900">[%s]</span>',
                    implode(', ', array_map('e', $simpleItems))
                );
            }
        }

        // Para arrays complexos ou grandes, mostrar apenas o número de itens
        return sprintf(
            '<span class="text-gray-600">%d %s</span> <button class="text-xs text-blue-600 ml-1" onclick="toggleDetails(this)">ver detalhes</button><div class="hidden mt-2"><pre class="text-xs bg-gray-100 p-2 rounded overflow-auto max-h-32">%s</pre></div>',
            $count,
            $count === 1 ? 'item' : 'itens',
            e(json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))
        );
    }
}

if (!function_exists('formatPhone')) {
    /**
     * Formata números de telefone
     *
     * @param string $phone
     * @return string
     */
    function formatPhone($phone)
    {
        $clean = preg_replace('/\D/', '', $phone);

        if (strlen($clean) === 11) {
            return sprintf('(%s) %s-%s',
                substr($clean, 0, 2),
                substr($clean, 2, 5),
                substr($clean, 7)
            );
        } elseif (strlen($clean) === 10) {
            return sprintf('(%s) %s-%s',
                substr($clean, 0, 2),
                substr($clean, 2, 4),
                substr($clean, 6)
            );
        }

        return $phone;
    }
}

if (!function_exists('formatDocument')) {
    /**
     * Formata documentos (CPF, CNPJ)
     *
     * @param string $type
     * @param string $value
     * @return string
     */
    function formatDocument($type, $value)
    {
        $clean = preg_replace('/\D/', '', $value);

        if ($type === 'cpf' && strlen($clean) === 11) {
            return sprintf('%s.%s.%s-%s',
                substr($clean, 0, 3),
                substr($clean, 3, 3),
                substr($clean, 6, 3),
                substr($clean, 9)
            );
        } elseif ($type === 'cnpj' && strlen($clean) === 14) {
            return sprintf('%s.%s.%s/%s-%s',
                substr($clean, 0, 2),
                substr($clean, 2, 3),
                substr($clean, 5, 3),
                substr($clean, 8, 4),
                substr($clean, 12)
            );
        }

        return $value;
    }
}

if (!function_exists('formatActivityAction')) {
    /**
     * Formata a ação da atividade com fallback para português
     *
     * @param string $action
     * @return string
     */
    function formatActivityAction($action)
    {
        $translation = __("activities.{$action}", [], 'pt-BR');

        // Se a tradução retornar a chave original, usar fallback
        if ($translation === "activities.{$action}") {
            $fallbacks = [
                'created' => 'criou',
                'updated' => 'atualizou',
                'deleted' => 'excluiu',
                'added' => 'adicionou',
                'removed' => 'removeu',
                'restored' => 'restaurou',
                'archived' => 'arquivou',
            ];

            return $fallbacks[$action] ?? $action;
        }

        return $translation;
    }
}

if (!function_exists('formatActivityModel')) {
    /**
     * Formata o modelo da atividade com fallback para português
     *
     * @param string $model
     * @return string
     */
    function formatActivityModel($model)
    {
        $translation = __("activities.models.{$model}", [], 'pt-BR');

        // Se a tradução retornar a chave original, usar fallback inteligente
        if ($translation === "activities.models.{$model}") {
            // Tentar remover namespace se existir
            $cleanModel = str_contains($model, '\\') ? class_basename($model) : $model;

            // Tentar novamente com o modelo limpo
            $retryTranslation = __("activities.models.{$cleanModel}", [], 'pt-BR');
            if ($retryTranslation !== "activities.models.{$cleanModel}") {
                return $retryTranslation;
            }

            // Fallback: converter CamelCase para texto legível em português
            return formatModelNameFallback($cleanModel);
        }

        return $translation;
    }
}

if (!function_exists('formatActivityAttribute')) {
    /**
     * Formata nomes de atributos para português com fallback inteligente
     *
     * @param string $attribute
     * @return string
     */
    function formatActivityAttribute($attribute)
    {
        $translation = __("activities.attributes.{$attribute}", [], 'pt-BR');

        // Se a tradução retornar a chave original, usar fallback inteligente
        if ($translation === "activities.attributes.{$attribute}") {
            return formatAttributeNameFallback($attribute);
        }

        return $translation;
    }
}

if (!function_exists('formatAttributeNameFallback')) {
    /**
     * Converte nomes de atributos snake_case ou camelCase para português
     *
     * @param string $attributeName
     * @return string
     */
    function formatAttributeNameFallback($attributeName)
    {
        // Mapeamentos específicos para campos comuns
        $specificMappings = [
            'id' => 'ID',
            'uuid' => 'UUID',
            'slug' => 'Slug',
            'hash' => 'Hash',
            'token' => 'Token',
            'api_token' => 'Token da API',
            'remember_token' => 'Token de Lembrar',
            'password' => 'Senha',
            'email' => 'E-mail',
            'name' => 'Nome',
            'created_at' => 'Criado em',
            'updated_at' => 'Atualizado em',
            'deleted_at' => 'Excluído em',
        ];

        if (isset($specificMappings[$attributeName])) {
            return $specificMappings[$attributeName];
        }

        // Converter snake_case para palavras separadas
        $words = explode('_', $attributeName);
        $words = array_map('strtolower', $words);

        // Traduzir palavras comuns
        $translations = [
            'id' => 'ID',
            'ids' => 'IDs',
            'data' => 'Data',
            'date' => 'Data',
            'hora' => 'Hora',
            'time' => 'Hora',
            'timestamp' => 'Data/Hora',
            'observacao' => 'Observação',
            'observacoes' => 'Observações',
            'observation' => 'Observação',
            'observations' => 'Observações',
            'descricao' => 'Descrição',
            'description' => 'Descrição',
            'nome' => 'Nome',
            'name' => 'Nome',
            'codigo' => 'Código',
            'code' => 'Código',
            'numero' => 'Número',
            'number' => 'Número',
            'num' => 'Número',
            'valor' => 'Valor',
            'value' => 'Valor',
            'preco' => 'Preço',
            'price' => 'Preço',
            'custo' => 'Custo',
            'cost' => 'Custo',
            'total' => 'Total',
            'subtotal' => 'Subtotal',
            'quantidade' => 'Quantidade',
            'quantity' => 'Quantidade',
            'qtd' => 'Quantidade',
            'status' => 'Status',
            'situacao' => 'Situação',
            'estado' => 'Estado',
            'state' => 'Estado',
            'tipo' => 'Tipo',
            'type' => 'Tipo',
            'categoria' => 'Categoria',
            'category' => 'Categoria',
            'subcategoria' => 'Subcategoria',
            'subcategory' => 'Subcategoria',
            'usuario' => 'Usuário',
            'user' => 'Usuário',
            'users' => 'Usuários',
            'filial' => 'Filial',
            'branch' => 'Filial',
            'departamento' => 'Departamento',
            'department' => 'Departamento',
            'cargo' => 'Cargo',
            'role' => 'Cargo',
            'funcao' => 'Função',
            'function' => 'Função',
            'setor' => 'Setor',
            'sector' => 'Setor',
            'centro' => 'Centro',
            'center' => 'Centro',
            'custo' => 'Custo',
            'cost' => 'Custo',
            'veiculo' => 'Veículo',
            'vehicle' => 'Veículo',
            'veiculos' => 'Veículos',
            'vehicles' => 'Veículos',
            'motorista' => 'Motorista',
            'driver' => 'Motorista',
            'motoristas' => 'Motoristas',
            'drivers' => 'Motoristas',
            'fornecedor' => 'Fornecedor',
            'supplier' => 'Fornecedor',
            'fornecedores' => 'Fornecedores',
            'suppliers' => 'Fornecedores',
            'produto' => 'Produto',
            'product' => 'Produto',
            'produtos' => 'Produtos',
            'products' => 'Produtos',
            'servico' => 'Serviço',
            'service' => 'Serviço',
            'servicos' => 'Serviços',
            'services' => 'Serviços',
            'ordem' => 'Ordem',
            'order' => 'Ordem',
            'ordens' => 'Ordens',
            'orders' => 'Ordens',
            'solicitacao' => 'Solicitação',
            'request' => 'Solicitação',
            'solicitacoes' => 'Solicitações',
            'requests' => 'Solicitações',
            'compra' => 'Compra',
            'purchase' => 'Compra',
            'compras' => 'Compras',
            'purchases' => 'Compras',
            'venda' => 'Venda',
            'sale' => 'Venda',
            'vendas' => 'Vendas',
            'sales' => 'Vendas',
            'estoque' => 'Estoque',
            'stock' => 'Estoque',
            'estoques' => 'Estoques',
            'stocks' => 'Estoques',
            'manutencao' => 'Manutenção',
            'maintenance' => 'Manutenção',
            'manutencoes' => 'Manutenções',
            'maintenances' => 'Manutenções',
            'abastecimento' => 'Abastecimento',
            'fuel' => 'Combustível',
            'abastecimentos' => 'Abastecimentos',
            'combustivel' => 'Combustível',
            'combustiveis' => 'Combustíveis',
            'pneu' => 'Pneu',
            'tire' => 'Pneu',
            'pneus' => 'Pneus',
            'tires' => 'Pneus',
            'inclusao' => 'Inclusão',
            'inclusion' => 'Inclusão',
            'exclusao' => 'Exclusão',
            'exclusion' => 'Exclusão',
            'atualizacao' => 'Atualização',
            'update' => 'Atualização',
            'criacao' => 'Criação',
            'creation' => 'Criação',
            'aprovacao' => 'Aprovação',
            'approval' => 'Aprovação',
            'rejeicao' => 'Rejeição',
            'rejection' => 'Rejeição',
            'cancelamento' => 'Cancelamento',
            'cancellation' => 'Cancelamento',
            'finalizacao' => 'Finalização',
            'completion' => 'Finalização',
            'aprovado' => 'Aprovado',
            'approved' => 'Aprovado',
            'rejeitado' => 'Rejeitado',
            'rejected' => 'Rejeitado',
            'pendente' => 'Pendente',
            'pending' => 'Pendente',
            'finalizado' => 'Finalizado',
            'finished' => 'Finalizado',
            'completed' => 'Finalizado',
            'cancelado' => 'Cancelado',
            'cancelled' => 'Cancelado',
            'ativo' => 'Ativo',
            'active' => 'Ativo',
            'inativo' => 'Inativo',
            'inactive' => 'Inativo',
            'urgente' => 'Urgente',
            'urgent' => 'Urgente',
            'normal' => 'Normal',
            'baixa' => 'Baixa',
            'baixo' => 'Baixo',
            'low' => 'Baixo',
            'media' => 'Média',
            'medio' => 'Médio',
            'medium' => 'Médio',
            'alta' => 'Alta',
            'alto' => 'Alto',
            'high' => 'Alto',
            'prioridade' => 'Prioridade',
            'priority' => 'Prioridade',
            'nivel' => 'Nível',
            'level' => 'Nível',
            'logs' => 'Logs',
            'log' => 'Log',
            'historico' => 'Histórico',
            'history' => 'Histórico',
            'endereco' => 'Endereço',
            'address' => 'Endereço',
            'telefone' => 'Telefone',
            'phone' => 'Telefone',
            'celular' => 'Celular',
            'mobile' => 'Celular',
            'email' => 'E-mail',
            'mail' => 'E-mail',
            'site' => 'Site',
            'website' => 'Site',
            'url' => 'URL',
            'link' => 'Link',
            'arquivo' => 'Arquivo',
            'file' => 'Arquivo',
            'arquivos' => 'Arquivos',
            'files' => 'Arquivos',
            'anexo' => 'Anexo',
            'attachment' => 'Anexo',
            'anexos' => 'Anexos',
            'attachments' => 'Anexos',
            'imagem' => 'Imagem',
            'image' => 'Imagem',
            'imagens' => 'Imagens',
            'images' => 'Imagens',
            'foto' => 'Foto',
            'photo' => 'Foto',
            'fotos' => 'Fotos',
            'photos' => 'Fotos',
            'documento' => 'Documento',
            'document' => 'Documento',
            'documentos' => 'Documentos',
            'documents' => 'Documentos',
            'relatorio' => 'Relatório',
            'report' => 'Relatório',
            'relatorios' => 'Relatórios',
            'reports' => 'Relatórios',
        ];

        $translatedWords = [];
        foreach ($words as $word) {
            $translatedWords[] = $translations[$word] ?? ucfirst($word);
        }

        return implode(' ', $translatedWords);
    }
}

if (!function_exists('formatModelNameFallback')) {
    /**
     * Converte nomes de modelos CamelCase para português
     *
     * @param string $modelName
     * @return string
     */
    function formatModelNameFallback($modelName)
    {
        // Mapeamentos específicos para casos comuns
        $specificMappings = [
            'User' => 'o usuário',
            'Branch' => 'a filial',
            'Role' => 'o cargo',
            'ActivityLog' => 'o log de atividade',
            'Permission' => 'a permissão',
            'PermissionGroup' => 'o grupo de permissão',
        ];

        if (isset($specificMappings[$modelName])) {
            return $specificMappings[$modelName];
        }

        // Converter CamelCase para palavras separadas
        $words = preg_split('/(?=[A-Z])/', $modelName, -1, PREG_SPLIT_NO_EMPTY);
        $words = array_map('strtolower', $words);

        // Traduzir palavras comuns
        $translations = [
            'tipo' => 'tipo de',
            'logs' => 'logs de',
            'log' => 'log de',
            'solicitacoes' => 'solicitações de',
            'solicitacao' => 'solicitação de',
            'compras' => 'compra',
            'compra' => 'compra',
            'categoria' => 'categoria',
            'sub' => 'sub',
            'veiculo' => 'veículo',
            'veiculos' => 'veículos',
            'manutencao' => 'manutenção',
            'ordem' => 'ordem de',
            'servico' => 'serviço',
            'servicos' => 'serviços',
            'estoque' => 'estoque',
            'produto' => 'produto',
            'produtos' => 'produtos',
            'pneu' => 'pneu',
            'pneus' => 'pneus',
            'fornecedor' => 'fornecedor',
            'fornecedores' => 'fornecedores',
            'motorista' => 'motorista',
            'motoristas' => 'motoristas',
            'abastecimento' => 'abastecimento',
            'abastecimentos' => 'abastecimentos',
            'combustivel' => 'combustível',
            'historico' => 'histórico',
            'nota' => 'nota',
            'fiscal' => 'fiscal',
            'pedido' => 'pedido',
            'item' => 'item',
            'itens' => 'itens',
            'transferencia' => 'transferência',
            'departamento' => 'departamento',
            'telefone' => 'telefone',
            'endereco' => 'endereço',
            'pessoal' => 'pessoal',
            'sinistro' => 'sinistro',
            'multa' => 'multa',
            'licenciamento' => 'licenciamento',
            'seguro' => 'seguro',
            'status' => 'status',
            'cadastro' => 'cadastro',
            'imobilizado' => 'imobilizado',
            'equipamento' => 'equipamento',
            'unidade' => 'unidade',
            'modelo' => 'modelo',
            'dimensao' => 'dimensão',
            'desenho' => 'desenho',
            'borracha' => 'borracha',
            'reforma' => 'reforma',
            'descarte' => 'descarte',
            'calibragem' => 'calibragem',
            'requisicao' => 'requisição',
            'contagem' => 'contagem',
            'inventario' => 'inventário',
            'deposito' => 'depósito',
            'controle' => 'controle',
            'vida' => 'vida',
            'eixos' => 'eixos',
            'tanque' => 'tanque',
            'bomba' => 'bomba',
            'encerrante' => 'encerrante',
            'recebimento' => 'recebimento',
            'acerto' => 'acerto',
            'orcamento' => 'orçamento',
            'planejamento' => 'planejamento',
            'inconsistencia' => 'inconsistência',
            'permissao' => 'permissão',
            'ajuste' => 'ajuste',
            'entrada' => 'entrada',
            'afericao' => 'aferição',
            'valor' => 'valor',
            'atual' => 'atual',
        ];

        $translatedWords = [];
        foreach ($words as $word) {
            $translatedWords[] = $translations[$word] ?? $word;
        }

        // Determinar artigo (o/a) baseado na última palavra
        $lastWord = end($translatedWords);
        $feminineSuffixes = ['a', 'ao', 'oes', 'ade', 'gem', 'cia', 'ncia'];

        $isFeminine = false;
        foreach ($feminineSuffixes as $suffix) {
            if (str_ends_with($lastWord, $suffix)) {
                $isFeminine = true;
                break;
            }
        }

        $article = $isFeminine ? 'a' : 'o';

        return $article . ' ' . implode(' ', $translatedWords);
    }
}
