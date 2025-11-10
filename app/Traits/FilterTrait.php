<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

trait FilterTrait
{
    protected function filter(Builder $query, Request $request): void
    {
        foreach ($request->query() as $key => $value) {
            if ($key === 'page' || !$request->filled($key)) {
                continue;
            }

            if ($this->isRelationFilter($key)) {
                if ($this->isDateColumn($query, $key)) {
                    $this->handleDateFilter($query, $key, $value);
                } else {
                    $this->handleRelationFilter($query, $key, $value);
                }
            } else {
                // Check if the column is a date field
                if ($this->isDateColumn($query, $key)) {
                    $this->handleDateFilter($query, $key, $value);
                } else {
                    $this->handleColumnFilter($query, $key, $value);
                }
            }
        }
    }

    private function isRelationFilter(string $key): bool
    {
        return strpos($key, '_') !== false;
    }

    private function isDateColumn(Builder $query, string $column): bool
    {
        $model = $query->getModel();
        $connection = $model->getConnection();
        $schemaBuilder = $connection->getSchemaBuilder();
        $table = $model->getTable();

        // Get column listing to find the type of the column
        $columns = $schemaBuilder->getColumnListing($table);
        foreach ($columns as $col) {
            if ($col === $column) {
                $type = $schemaBuilder->getColumnType($table, $col);
                return in_array($type, ['date', 'datetime', 'timestamp']);
            }
        }
        return false;
    }

    private function handleRelationFilter(Builder $query, string $key, string $value): void
    {
        [$relation, $field] = $this->parseRelationAndField($key);

        if (method_exists($query->getModel(), $relation)) {
            $this->applySubqueryFilter($query, $relation, $field, $value);
        } else {
            $this->applyColumnFilter($query, $key, $value);
        }
    }

    private function handleDateFilter(Builder $query, string $column, string $value): void
    {
        $date = Carbon::parse($value)->format('Y-m-d');
            $query->whereDate($column, $date);
    }

    private function handleColumnFilter(Builder $query, string $column, string $value): void
    {
        if ($this->columnExists($query, $column)) {
            $query->where($column, 'like', "%{$value}%");
        }
    }

    private function parseRelationAndField(string $key): array
    {
        $parts = explode('_', $key);
        $relation = array_shift($parts);
        $field = implode('_', $parts);

        return [$relation, $field];
    }

    private function columnExists(Builder $query, string $column): bool
    {
        return $query->getModel()->getConnection()->getSchemaBuilder()->hasColumn($query->getModel()->getTable(), $column);
    }

    protected function applyColumnFilter(Builder $query, string $column, string $value): void
    {
        $query->where($column, 'like', "%{$value}%");
    }

    protected function applySubqueryFilter(Builder $query, string $relation, string $relationKey, string $value): void
    {
        $query->whereHas($relation, function ($subQuery) use ($relationKey, $value) {
            if ($this->isDateColumn($subQuery, $relationKey)) {
                $this->handleDateFilter($subQuery, $relationKey, $value);
            } elseif ($this->columnExists($subQuery, $relationKey)) {
                $subQuery->where($relationKey, 'like', "%{$value}%");
            }
        });
    }
}
