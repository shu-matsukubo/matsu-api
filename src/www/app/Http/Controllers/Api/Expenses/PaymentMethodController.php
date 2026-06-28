<?php

namespace App\Http\Controllers\Api\Expenses;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Expenses\PaymentMethodResource;
use App\Services\Expenses\PaymentMethodService;
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
    public function show(int|string $id): void
    {
        // 将来のために念のため残しておく
    }

    /*
    * POST用ルート
    */
    public function store(Request $request): void
    {
        // 将来のために念のため残しておく
    }

    /*
    * PUT/UPDATE用ルート
    */
    public function update(Request $request, int|string $id): void
    {
        // 将来のために念のため残しておく
    }

    /*
    * DELETE用ルート
    */
    public function destroy(int|string $id): void
    {
        // 将来のために念のため残しておく
    }
}
