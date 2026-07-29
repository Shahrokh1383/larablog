<?php

namespace Modules\Identity\Http\Controllers\Api;

use Modules\Identity\Http\Requests\ForgotPasswordRequest;
use Modules\Identity\Http\Requests\ResetPasswordRequest;
use Modules\Identity\Services\PasswordResetService;
use Modules\Identity\DTOs\ForgotPasswordDTO;
use Modules\Identity\DTOs\ResetPasswordDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class PasswordResetController extends Controller
{
    public function __construct(protected PasswordResetService $service) {}

    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        $dto = new ForgotPasswordDTO(...$request->validated());
        $message = $this->service->sendResetLink($dto);

        return response()->json(['message' => $message]);
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $dto = new ResetPasswordDTO(...$request->validated());
        $message = $this->service->reset($dto);

        return response()->json(['message' => $message]);
    }
}