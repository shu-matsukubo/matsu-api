<?php

namespace App\Http\Controllers\Api\Expenses;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Expenses\PaymentMethodResource;
use App\Services\Expenses\PaymentMethodService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentMethodController extends BaseApiController
{
    private PaymentMethodService $paymentMethodService;

    /*
    * コンストラクタ
    */
    public function __construct(PaymentMethodService $paymentMethodService)
    {
        $this->paymentMethodService = $paymentMethodService;
    }

    /*
    * GET用ルート
    */
    public function index(): AnonymousResourceCollection
    {
        // 一覧を取得
        $categories = $this->paymentMethodService->list();

        return PaymentMethodResource::collection($categories);
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
