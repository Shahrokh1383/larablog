<?php

namespace Modules\Identity\Http\Controllers\Api;

use Modules\Identity\Services\OAuthService;
use Modules\Identity\DTOs\OAuthCallbackDTO;
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

    public function callback(Request $request, string $provider): RedirectResponse
    {
        $dto = new OAuthCallbackDTO(
            provider: $provider,
            code: $request->input('code'),
            state: $request->input('state')
        );

        $result = $this->oauthService->callback($dto);

        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
        
        // Redirect to frontend with token
        return redirect()->to("{$frontendUrl}/oauth-callback?token={$result['token']}");
    }
}