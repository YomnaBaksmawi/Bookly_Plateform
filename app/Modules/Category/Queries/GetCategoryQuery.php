<?php

namespace App\Modules\Category\Queries;

use App\Modules\Category\Models\Category;

class GetCategoryQuery
{
    public function execute(Category $category): Category
    {
        return $category;
    }
}