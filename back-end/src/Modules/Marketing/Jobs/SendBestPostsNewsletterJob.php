<?php

namespace Modules\Marketing\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Marketing\Models\Subscriber;
use Modules\Marketing\Mail\BestPostsMail;
use Modules\Marketing\Exceptions\MarketingException;
use Modules\Content\Services\Contracts\PostInfoContract;
use Illuminate\Support\Facades\Mail;

class SendBestPostsNewsletterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?array $subscriberIds = null,
        public bool $sendToAll = false
    ) {}

    public function handle(PostInfoContract $postInfoService): void
    {
        $posts = $postInfoService->getTopPostsOfWeek(5);

        if (empty($posts)) {
            return; // No top posts to send
        }

        $query = Subscriber::where('is_active', true);

        if (!$this->sendToAll && !empty($this->subscriberIds)) {
            $query->whereIn('id', $this->subscriberIds);
        } elseif (!$this->sendToAll && empty($this->subscriberIds)) {
            throw MarketingException::noSubscribersFound();
        }

        // Chunking to prevent memory exhaustion on large subscriber lists
        $query->chunk(100, function ($subscribers) use ($posts) {
            foreach ($subscribers as $subscriber) {
                Mail::to($subscriber->email)->queue(new BestPostsMail($posts));
            }
        });
    }
}