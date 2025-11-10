<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait ExportableTrait
{
    /**
     * Verifica se algum filtro foi aplicado na requisição
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
     * Verificar se a exportação excede o limite recomendado
     */
    protected function exceedsExportLimit(Builder $query, int $limit = 1000): bool
    {
        // Contar apenas as linhas, não todos os dados
        $count = $query->count();

        return $count > $limit;
    }

    /**
     * Exportar para PDF com suporte a confirmação para muitos registros
     *
     * @param  array  $allowedFilters  Filtros permitidos para exportação
     * @return mixed
     */
    protected function exportToPdf(
        Request $request,
        Builder $query,
        string $view,
        string $filename,
        array $allowedFilters = [],
        int $limit = 1000,
        array $options = []
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
            // Definir configurações padrão para PDF
            $defaultOptions = [
                'paper' => 'a4',
                'orientation' => 'landscape',
                'options' => [
                    'defaultFont' => 'sans-serif',
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'isPhpEnabled' => true,
                    'chroot' => public_path(),
                ],
            ];

            // Mesclar opções padrão com as fornecidas
            $options = array_merge($defaultOptions, $options);

            // Configurar o PDF
            $pdf = app('dompdf.wrapper');
            $pdf->setPaper($options['paper'], $options['orientation']);
            $pdf->setOptions($options['options']);

            try {
                // Carregar a view com os dados
                $data = $query->get();
                $pdf->loadView($view, ['data' => $data]);

                // Retornar o PDF para download
                return $pdf->download($filename.'_'.date('Y-m-d_His').'.pdf');
            } catch (\Exception $e) {
                // Registrar o erro para diagnóstico
                \Illuminate\Support\Facades\Log::error('Erro ao gerar PDF: '.$e->getMessage());

                // Redirecionar com mensagem de erro amigável
                return back()->with([
                    'error' => 'Não foi possível gerar o PDF. Erro: '.$e->getMessage(),
                    'export_error' => true,
                ]);
            }
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
     * Exportar para CSV com suporte a streaming para grandes volumes
     *
     * @param  array  $allowedFilters  Filtros permitidos para exportação
     * @return mixed
     */
    protected function exportToCsv(
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
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="'.$filename.'_'.date('Y-m-d_His').'.csv"',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];

            // Usar streaming para evitar carregamento de todos os dados na memória
            return response()->stream(function () use ($query, $columns) {
                $handle = fopen('php://output', 'w');

                // Escrever cabeçalhos
                fputcsv($handle, array_values($columns));

                // Processar em lotes de 100 registros para não sobrecarregar a memória
                $query->chunk(100, function ($records) use ($handle, $columns) {
                    foreach ($records as $record) {
                        $row = [];
                        foreach ($columns as $key => $label) {
                            $value = data_get($record, $key);
                            // Formatar datas se necessário
                            if ($value instanceof \DateTime) {
                                $value = $value->format('d/m/Y H:i');
                            }
                            $row[] = $value;
                        }
                        fputcsv($handle, $row);
                    }
                });

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

    /**
     * Exportar para Excel com suporte a grandes volumes
     *
     * @param  Builder  $query
     * @param  array  $allowedFilters  Filtros permitidos para exportação
     * @return mixed
     */
    protected function exportToExcel(
        Request $request,
        $data, // pode ser Builder ou Collection
        array $columns,
        string $filename,
        array $allowedFilters = [],
        int $limit = 1000
    ) {
        // Caso seja uma Collection (dados já prontos, como agrupados)
        if ($data instanceof \Illuminate\Support\Collection) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();

            // Cabeçalhos
            $col = 'A';
            foreach ($columns as $label) {
                $sheet->setCellValue($col.'1', $label);
                $col++;
            }

            // Linhas de dados
            $row = 2;
            foreach ($data as $record) {
                $col = 'A';
                foreach ($columns as $key => $label) {
                    $value = $record[$key] ?? null;
                    $sheet->setCellValue($col.$row, $value);
                    $col++;
                }
                $row++;
            }

            // Estilo simples (auto width)
            foreach (range('A', $col) as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }

            // Download
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $fileName = $filename.'_'.date('Y-m-d_His').'.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$fileName.'"');
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
            exit;
        }

        // Caso seja uma Query (modo antigo)
        if (! $this->hasAnyFilter($request, $allowedFilters)) {
            return back()->with([
                'error' => 'É necessário aplicar pelo menos um filtro antes de exportar.',
                'export_error' => true,
            ]);
        }

        if ($request->has('confirmed') || ! $this->exceedsExportLimit($data, $limit)) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();

            // Cabeçalhos
            $col = 'A';
            foreach ($columns as $label) {
                $sheet->setCellValue($col.'1', $label);
                $col++;
            }

            $row = 2;
            $data->chunk(100, function ($records) use (&$row, $sheet, $columns) {
                foreach ($records as $record) {
                    $col = 'A';
                    foreach ($columns as $key => $label) {
                        $value = data_get($record, $key);
                        if ($value instanceof \DateTime) {
                            $value = $value->format('d/m/Y H:i');
                        }
                        $sheet->setCellValue($col.$row, $value);
                        $col++;
                    }
                    $row++;
                }
            });

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $fileName = $filename.'_'.date('Y-m-d_His').'.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$fileName.'"');
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
            exit;
        } else {
            $currentUrl = $request->fullUrlWithQuery(['confirmed' => 1]);

            return back()->with([
                'warning' => "Você está tentando exportar mais de {$limit} registros, o que pode levar mais tempo.",
                'export_confirmation' => true,
                'export_url' => $currentUrl,
            ]);
        }
    }

    /**
     * Exportar para XML com suporte a grandes volumes
     *
     * @param  array  $allowedFilters  Filtros permitidos para exportação
     * @return mixed
     */
    protected function exportToXml(Request $request, Builder $query, array $structure, string $rootElement, string $itemElement, string $filename, array $allowedFilters = [], int $limit = 1000)
    {
        // Verificar se algum filtro foi aplicado
        if (! $this->hasAnyFilter($request, $allowedFilters)) {
            return back()->with([
                'error' => 'É necessário aplicar pelo menos um filtro antes de exportar.',
                'export_error' => true,
            ]);
        }

        // Verificar se foi confirmado ou se não excede o limite
        if ($request->has('confirmed') || ! $this->exceedsExportLimit($query, $limit)) {
            // Iniciar a resposta XML
            header('Content-Type: application/xml; charset=utf-8');
            header('Content-Disposition: attachment; filename="'.$filename.'_'.date('Y-m-d_His').'.xml"');

            // Criar o documento XML
            echo '<?xml version="1.0" encoding="UTF-8"?>', PHP_EOL;
            echo '<', $rootElement, '>', PHP_EOL;

            // Processar em lotes para não sobrecarregar a memória
            $query->chunk(100, function ($records) use ($structure, $itemElement) {
                foreach ($records as $record) {
                    echo '  <', $itemElement, '>', PHP_EOL;

                    foreach ($structure as $xmlTag => $fieldPath) {
                        $value = data_get($record, $fieldPath);

                        // Formatar datas se necessário
                        if ($value instanceof \DateTime) {
                            $value = $value->format('d/m/Y H:i');
                        }

                        // Escapar o conteúdo XML
                        $value = htmlspecialchars($value ?? '', ENT_XML1 | ENT_QUOTES, 'UTF-8');

                        echo '    <', $xmlTag, '>', $value, '</', $xmlTag, '>', PHP_EOL;
                    }

                    echo '  </', $itemElement, '>', PHP_EOL;
                }
            });

            echo '</', $rootElement, '>', PHP_EOL;
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
}
