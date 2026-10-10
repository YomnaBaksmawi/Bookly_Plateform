<?php

namespace App\Modules\Category\Actions;

use App\Modules\Category\Models\Category;

class DeleteCategoryAction
{
    public function execute(Category $category): void
    {
        $category->delete();
    }
}