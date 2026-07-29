<?php

namespace Modules\Identity\Http\Controllers\Api;

use Modules\Identity\Services\OAuthService;
use Modules\Identity\DTOs\OAuthCallbackDTO;
use Modules\Identity\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class OAuthController extends Controller
{
    public function __construct(protected OAuthService $oauthService) {}

    public function redirect(string $provider): RedirectResponse
    {
        return $this->oauthService->redirect($provider);
    }

    public function callback(Request $request, string $provider): JsonResponse
    {
        $dto = new OAuthCallbackDTO(
            provider: $provider,
            code: $request->input('code'),
            state: $request->input('state')
        );

        $result = $this->oauthService->callback($dto);

        return response()->json([
            'user'  => new UserResource($result['user']),
            'token' => $result['token'],
        ]);
    }
}