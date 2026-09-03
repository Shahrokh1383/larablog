<?php

namespace Modules\Marketing\Services;

use Modules\Marketing\Models\Subscriber;
use Modules\Marketing\DTOs\SubscribeDTO;
use Modules\Marketing\Actions\SubscribeToNewsletterAction;
use Modules\Marketing\Exceptions\MarketingException;
use Modules\Marketing\Jobs\SendBestPostsNewsletterJob;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NewsletterService
{
    private const MAX_PER_PAGE = 100;

    public function __construct(
        private readonly SubscribeToNewsletterAction $subscribeAction
    ) {}

    public function subscribe(SubscribeDTO $dto): Subscriber
    {
        return $this->subscribeAction->execute($dto);
    }

    public function getAdminSubscribers(int $perPage = 20): LengthAwarePaginator
    {
        return Subscriber::latest()->paginate(
            max(1, min(self::MAX_PER_PAGE, $perPage))
        );
    }

    public function dispatchNewsletterJob(?array $subscriberIds = null, bool $sendToAll = false): void
    {
        if (!$sendToAll && empty($subscriberIds)) {
            throw MarketingException::subscriberSelectionRequired();
        }

        SendBestPostsNewsletterJob::dispatch($subscriberIds, $sendToAll);
    }

    public function deleteSubscriber(Subscriber $subscriber): void
    {
        $subscriber->delete();
    }

    public function toggleStatus(Subscriber $subscriber): void
    {
        $subscriber->update(['is_active' => !$subscriber->is_active]);
    }
}