<?php

namespace App\Http\Controllers\Api\Expenses;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Expenses\CategoryResource;
use App\Services\Expenses\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends BaseApiController
{
    private CategoryService $categoryService;

    /*
    * コンストラクタ
    */
    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /*
    * GET用ルート
    */
    public function index(): AnonymousResourceCollection
    {
        // 一覧を取得
        $categories = $this->categoryService->list();

        return CategoryResource::collection($categories);
    }

    /*
    * GET用ルート（特定のID検索）
    */
    public function show(string $id): JsonResponse
    {
        // 将来のために念のため残しておく
        return response()->json();
    }

    /*
    * POST用ルート
    */
    public function store(Request $request): JsonResponse
    {
        // 将来のために念のため残しておく
        return response()->json();
    }

    /*
    * PUT/UPDATE用ルート
    */
    public function update(Request $request, string $id): JsonResponse
    {
        // 将来のために念のため残しておく
        return response()->json();
    }

    /*
    * DELETE用ルート
    */
    public function destroy(string $id): JsonResponse
    {
        // 将来のために念のため残しておく
        return response()->json();
    }
}
