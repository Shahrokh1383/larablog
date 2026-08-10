<?php

namespace Modules\Marketing\Services;

use Modules\Marketing\Models\Subscriber;
use Modules\Marketing\DTOs\SubscribeDTO;
use Modules\Marketing\Actions\SubscribeToNewsletterAction;
use Modules\Marketing\Jobs\SendBestPostsNewsletterJob;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NewsletterService
{
    public function __construct(
        private readonly SubscribeToNewsletterAction $subscribeAction
    ) {}

    public function subscribe(SubscribeDTO $dto): Subscriber
    {
        return $this->subscribeAction->execute($dto);
    }

    public function getAdminSubscribers(int $perPage = 20): LengthAwarePaginator
    {
        return Subscriber::latest()->paginate($perPage);
    }

    public function dispatchNewsletterJob(?array $subscriberIds = null, bool $sendToAll = false): void
    {
        SendBestPostsNewsletterJob::dispatch($subscriberIds, $sendToAll);
    }

    public function deleteSubscriber(Subscriber $subscriber): void
    {
        $subscriber->delete();
    }
}