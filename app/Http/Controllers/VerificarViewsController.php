<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;

class VerificarViewsController extends Controller
{
    /**
     * Verifica se as views necessárias existem
     */
    public function verificarViews()
    {
        $viewsParaVerificar = [
            'admin.estoque-combustivel.dashboard',
        ];

        $resultados = [];

        foreach ($viewsParaVerificar as $view) {
            $exists = View::exists($view);
            $path = $this->getViewPath($view);

            $resultados[$view] = [
                'exists' => $exists,
                'path' => $path,
                'file_exists' => $path ? File::exists($path) : false,
            ];
        }

        // Verificar componentes específicos
        $components = [
            'x-app-layout',
            'x-slot',
            'x-icons.refresh',
            'x-help-icon',
            'x-ui.fuel-gauge',
            'x-text-input',
            'x-input-label',
            'x-primary-button',
        ];

        $componentResults = [];
        foreach ($components as $component) {
            $componentResults[$component] = $this->checkComponentExists($component);
        }

        return response()->json([
            'views' => $resultados,
            'components' => $componentResults,
            'view_paths' => config('view.paths'),
            'namespace' => View::getFinder()->getHints(),
        ]);
    }

    /**
     * Obtém o caminho real da view
     */
    private function getViewPath($view)
    {
        $normalizedView = str_replace('.', '/', $view);

        foreach (config('view.paths') as $path) {
            $fullPath = $path.'/'.$normalizedView.'.blade.php';
            if (File::exists($fullPath)) {
                return $fullPath;
            }
        }

        return null;
    }

    /**
     * Verifica se um componente existe
     */
    private function checkComponentExists($component)
    {
        // Checar para componentes anônimos
        $viewPaths = config('view.paths');
        $componentPaths = [];

        foreach ($viewPaths as $path) {
            $componentPaths[] = $path.'/components';
        }

        // Verificar no diretório components
        $componentFile = str_replace('-', '/', str_replace('x-', '', $component));

        foreach ($componentPaths as $path) {
            $fullPath = $path.'/'.$componentFile.'.blade.php';
            if (File::exists($fullPath)) {
                return [
                    'exists' => true,
                    'path' => $fullPath,
                    'type' => 'blade',
                ];
            }
        }

        // Verificar em componentes de classe
        $classComponent = ucfirst(str_replace('-', '', str_replace('x-', '', $component)));

        if (class_exists("\\App\\View\\Components\\{$classComponent}")) {
            return [
                'exists' => true,
                'path' => "\\App\\View\\Components\\{$classComponent}",
                'type' => 'class',
            ];
        }

        return [
            'exists' => false,
            'path' => null,
            'type' => null,
        ];
    }

    /**
     * Cria uma view base para testar
     */
    public function criarViewBase()
    {
        $viewsDir = resource_path('views/admin/estoque-combustivel');

        if (! File::exists($viewsDir)) {
            File::makeDirectory($viewsDir, 0755, true);
        }

        $content = <<<'EOT'
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard de Estoque de Combustível') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Teste de View</h3>
                    <p class="mt-2 text-gray-600">Esta é uma view de teste para verificar se está funcionando corretamente.</p>
                    
                    <div class="mt-4">
                        <h4 class="font-medium text-gray-800">Informações de Debug:</h4>
                        <ul class="mt-2 list-disc list-inside">
                            <li>User ID: {{ Auth::id() }}</li>
                            <li>Is Admin: {{ Auth::user()->hasRole('admin') ? 'Sim' : 'Não' }}</li>
                            <li>Is Superuser: {{ Auth::user()->isSuperuser() ? 'Sim' : 'Não' }}</li>
                            <li>Permissões: {{ implode(', ', Auth::user()->permissions->pluck('name')->toArray()) }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
EOT;

        File::put($viewsDir.'/dashboard.blade.php', $content);

        return response()->json([
            'success' => true,
            'message' => 'View de teste criada com sucesso',
            'path' => $viewsDir.'/admin.dashboard.blade.php',
        ]);
    }
}
