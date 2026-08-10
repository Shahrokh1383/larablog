<?php

namespace Modules\Marketing\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Modules\Marketing\Services\NewsletterService;
use Modules\Marketing\Services\ContactService;
use Modules\Marketing\Models\Subscriber;
use Modules\Marketing\Models\ContactMessage;
use Modules\Marketing\Http\Requests\SendNewsletterRequest;
use Modules\Marketing\Http\Resources\SubscriberResource;
use Modules\Marketing\Http\Resources\ContactMessageResource;

class MarketingAdminController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private NewsletterService $newsletterService,
        private ContactService $contactService
    ) {}

    public function subscribers(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Subscriber::class); // Requires Policy
        $subscribers = $this->newsletterService->getAdminSubscribers($request->query('per_page', 20));
        return SubscriberResource::collection($subscribers)->response()->setStatusCode(200);
    }

    public function sendNewsletter(SendNewsletterRequest $request): JsonResponse
    {
        $this->newsletterService->dispatchNewsletterJob(
            subscriberIds: $request->validated('subscriber_ids'),
            sendToAll: $request->validated('send_to_all')
        );
        return response()->json(['message' => 'Newsletter dispatch job queued successfully.'], 202);
    }

    public function deleteSubscriber(Subscriber $subscriber): JsonResponse
    {
        $this->authorize('delete', $subscriber);
        $this->newsletterService->deleteSubscriber($subscriber);
        return response()->json(null, 204);
    }

    public function contactMessages(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ContactMessage::class);
        $messages = $this->contactService->getAdminMessages($request->query('per_page', 20));
        return ContactMessageResource::collection($messages)->response()->setStatusCode(200);
    }

    public function showContactMessage(ContactMessage $message): JsonResponse
    {
        $this->authorize('view', $message);
        $this->contactService->markAsRead($message);
        return (new ContactMessageResource($message))->response()->setStatusCode(200);
    }

    public function deleteContactMessage(ContactMessage $message): JsonResponse
    {
        $this->authorize('delete', $message);
        $this->contactService->deleteMessage($message);
        return response()->json(null, 204);
    }
}