<?php

namespace App\Http\Controllers\Api\Expenses;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Expenses\CategoryResource;
use App\Services\Expenses\CategoryService;
use Illuminate\Http\Request;

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
    public function index()
    {
        // 一覧を取得
        $categories = $this->categoryService->list();

        return CategoryResource::collection($categories);
    }

    /*
    * GET用ルート（特定のID検索）
    */
    public function show($id)
    {
        // 将来のために念のため残しておく
    }

    /*
    * POST用ルート
    */
    public function store(Request $request)
    {
        // 将来のために念のため残しておく
    }

    /*
    * PUT/UPDATE用ルート
    */
    public function update(Request $request, $id)
    {
        // 将来のために念のため残しておく
    }

    /*
    * DELETE用ルート
    */
    public function destroy($id)
    {
        // 将来のために念のため残しておく
    }
}
