<?php

namespace App\Http\Controllers\Api\Expenses;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Expenses\Expense;
use App\Services\Expenses\ExpenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExpensesController extends BaseApiController
{
    private ExpenseService $expenseService;

    /*
    * コンストラクタ
    */
    public function __construct(ExpenseService $expenseService)
    {
        $this->expenseService = $expenseService;
    }

    /*
    * GET用ルート
    */
    public function index(Request $request): AnonymousResourceCollection
    {
        $mode = (string) $request->query('mode', 'summary');

        /** @var array<string, mixed> $params */
        $params = $request->all();

        return $this->expenseService->getExpensesByMode($mode, $params);
    }

    /*
    * GET用ルート（特定のID検索）
    */
    public function show(string $id): JsonResponse
    {
        return response()->json();
    }

    /*
    * POST用ルート
    */
    public function store(Request $request): Expense
    {
        /** @var array<string, mixed> $data */
        $data = $request->all();

        return $this->expenseService->create($data);
    }

    /*
    * PUT/UPDATE用ルート
    */
    public function update(Request $request, string $id): JsonResponse
    {
        return response()->json();
    }

    /*
    * DELETE用ルート
    */
    public function destroy(Expense $expense): JsonResponse
    {
        $this->expenseService->delete($expense);

        return response()->json(['result' => 1], 200);
    }
}
