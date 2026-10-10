<?php

namespace App\Modules\Category\Actions;

use App\Modules\Category\Models\Category;

class UpdateCategoryAction
{
    public function execute(Category $category, array $data): Category
    {
        $category->update($data);

        return $category->fresh();
    }
}