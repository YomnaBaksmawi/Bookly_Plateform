<?php

namespace App\Modules\Category\Queries;

use App\Modules\Category\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class ListCategoriesQuery
{
    public function execute(): Collection
    {
        return Category::latest()->get();
    }
}