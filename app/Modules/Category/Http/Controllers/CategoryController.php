<?php

namespace App\Modules\Category\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Category\Actions\CreateCategoryAction;
use App\Modules\Category\Actions\DeleteCategoryAction;
use App\Modules\Category\Actions\UpdateCategoryAction;
use App\Modules\Category\Http\Requests\StoreCategoryRequest;
use App\Modules\Category\Http\Requests\UpdateCategoryRequest;
use App\Modules\Category\Http\Resources\CategoryResource;
use App\Modules\Category\Models\Category;
use App\Modules\Category\Queries\GetCategoryQuery;
use App\Modules\Category\Queries\ListCategoriesQuery;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CreateCategoryAction $createCategoryAction,
        private readonly UpdateCategoryAction $updateCategoryAction,
        private readonly DeleteCategoryAction $deleteCategoryAction,
        private readonly ListCategoriesQuery $listCategoriesQuery,
        private readonly GetCategoryQuery $getCategoryQuery,
    ) {}

    public function index(): JsonResponse
    {
        $categories = $this->listCategoriesQuery->execute();

        return response()->json([
            'success' => true,
            'data' => CategoryResource::collection($categories),
        ]);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->createCategoryAction->execute(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully.',
            'data' => new CategoryResource($category),
        ], 201);
    }

    public function show(Category $category): JsonResponse
    {
        $category = $this->getCategoryQuery->execute($category);

        return response()->json([
            'success' => true,
            'data' => new CategoryResource($category),
        ]);
    }

    public function update( UpdateCategoryRequest $request, Category $category ): JsonResponse {
        $category = $this->updateCategoryAction->execute( $category, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully.',
            'data' => new CategoryResource($category),
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->deleteCategoryAction->execute($category);

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully.',
        ]);
    }
}