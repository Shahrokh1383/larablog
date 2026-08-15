<?php

namespace Modules\Identity\Http\Controllers\Api;

use Illuminate\Http\RedirectResponse as IlluminateRedirectResponse;
use Illuminate\Routing\Controller;
use Modules\Identity\DTOs\OAuthCallbackDTO;
use Modules\Identity\Http\Requests\OAuthCallbackRequest;
use Modules\Identity\Services\OAuthService;
use Symfony\Component\HttpFoundation\RedirectResponse;

class OAuthController extends Controller
{
    public function __construct(protected OAuthService $oauthService) {}

    public function redirect(string $provider): RedirectResponse
    {
        return $this->oauthService->redirect($provider);
    }

    public function callback(OAuthCallbackRequest $request, string $provider): IlluminateRedirectResponse
    {
        $dto = new OAuthCallbackDTO(
            provider: $provider,
            code: $request->validated('code'),
        );

        $this->oauthService->callback($dto);

        $frontendUrl = config('app.frontend_url');

        return redirect()->to("{$frontendUrl}/oauth-callback");
    }
}