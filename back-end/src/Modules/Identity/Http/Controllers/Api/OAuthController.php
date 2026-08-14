<?php

namespace Modules\Identity\Http\Controllers\Api;

use Modules\Identity\Http\Requests\OAuthCallbackRequest;
use Modules\Identity\Services\OAuthService;
use Modules\Identity\DTOs\OAuthCallbackDTO;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

class OAuthController extends Controller
{
    public function __construct(protected OAuthService $oauthService) {}

    public function redirect(string $provider): RedirectResponse
    {
        return $this->oauthService->redirect($provider);
    }

    public function callback(OAuthCallbackRequest $request, string $provider): RedirectResponse
    {
        $dto = new OAuthCallbackDTO(
            provider: $provider,
            code: $request->validated('code'),
            state: $request->validated('state'),
        );

        $this->oauthService->callback($dto);

        $frontendUrl = config('app.frontend_url');

        return redirect()->to("{$frontendUrl}/oauth-callback");
    }
}