<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait Searchable
{
    public function scopeQuickSearch($query, $searchTerm)
    {
        return $query->where($this->getSearchField(), 'like', '%' . $searchTerm . '%');
    }

    public function getSearchField()
    {
        // Retorne o campo que você deseja buscar
        return 'nome';
    }
}
