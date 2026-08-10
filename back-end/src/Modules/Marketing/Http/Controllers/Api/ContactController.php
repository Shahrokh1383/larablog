<?php

namespace Modules\Marketing\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Marketing\Services\ContactService;
use Modules\Marketing\Http\Requests\StoreContactMessageRequest;
use Modules\Marketing\DTOs\ContactMessageDTO;

class ContactController extends Controller
{
    public function __construct(private ContactService $contactService) {}

    public function store(StoreContactMessageRequest $request): JsonResponse
    {
        $user = $request->user();

        $dto = new ContactMessageDTO(
            name: $user->name ?? $request->validated('name'),
            email: $user->email ?? $request->validated('email'),
            subject: $request->validated('subject'),
            message: $request->validated('message'),
            userId: $user?->id
        );

        $this->contactService->submitMessage($dto);

        return response()->json(['message' => 'Your message has been sent successfully.'], 201);
    }
}