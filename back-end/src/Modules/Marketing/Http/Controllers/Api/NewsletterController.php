<?php

namespace Modules\Marketing\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Marketing\Services\NewsletterService;
use Modules\Marketing\Http\Requests\StoreSubscriberRequest;
use Modules\Marketing\DTOs\SubscribeDTO;
use Modules\Marketing\Exceptions\MarketingException;

class NewsletterController extends Controller
{
    public function __construct(private NewsletterService $newsletterService) {}

    public function subscribe(StoreSubscriberRequest $request): JsonResponse
    {
        try {
            $dto = new SubscribeDTO(email: $request->validated('email'));
            $this->newsletterService->subscribe($dto);
            return response()->json(['message' => 'Successfully subscribed to the newsletter.'], 201);
        } catch (MarketingException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
}