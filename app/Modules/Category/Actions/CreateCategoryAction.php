<?php

namespace App\Modules\Category\Actions;

use App\Modules\Category\Models\Category;

class CreateCategoryAction
{
    public function execute(array $data): Category
    {
        return Category::create($data);
    }
}