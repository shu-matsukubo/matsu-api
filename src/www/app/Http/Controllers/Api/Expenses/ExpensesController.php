<?php

namespace App\Http\Controllers\Api\Expenses;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Expenses\Expense;
use App\Services\Expenses\ExpenseService;
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
        $mode = $request->query('mode', 'summary');

        return $this->expenseService->getExpensesByMode($mode, $request->all());
    }

    /*
    * GET用ルート（特定のID検索）
    */
    public function show($id)
    {
        //
    }

    /*
    * POST用ルート
    */
    public function store(Request $request)
    {
        return $this->expenseService->create($request->all());
    }

    /*
    * PUT/UPDATE用ルート
    */
    public function update(Request $request, $id)
    {
        //
    }

    /*
    * DELETE用ルート
    */
    public function destroy(Expense $expense)
    {
        $this->expenseService->delete($expense);

        return response()->json(['result' => 1], 200);
    }
}
