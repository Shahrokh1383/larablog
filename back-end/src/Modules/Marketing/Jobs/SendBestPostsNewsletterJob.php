<?php

namespace Modules\Marketing\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\Articles\Services\Contracts\PostInfoContract;
use Modules\Marketing\Mail\BestPostsMail;
use Modules\Marketing\Models\NewsletterSend;
use Modules\Marketing\Models\Subscriber;
use Throwable;

class SendBestPostsNewsletterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const TOP_POST_COUNT = 5;
    private const CHUNK_SIZE = 100;

    public string $runId;

    public function __construct(
        public ?array $subscriberIds = null,
        public bool $sendToAll = false,
    ) {
        $this->runId = (string) Str::uuid();
    }

    public function handle(PostInfoContract $postInfoService): void
    {
        $posts = $postInfoService->getTopPostsOfWeek(self::TOP_POST_COUNT);

        if (empty($posts)) {
            Log::info('Marketing newsletter run skipped: no top posts this week.', [
                'run_id' => $this->runId,
            ]);
            return;
        }

        $query = Subscriber::query()->where('is_active', true);

        if (!$this->sendToAll) {
            $query->whereIn('id', $this->subscriberIds ?? []);
        }

        $sent = 0;
        $skipped = 0;

        $query->chunkById(self::CHUNK_SIZE, function (Collection $subscribers) use ($posts, &$sent, &$skipped): void {
            foreach ($subscribers as $subscriber) {
                $queued = $this->queueMailForSubscriber($subscriber, $posts);
                $queued ? $sent++ : $skipped++;
            }
        });

        if ($sent + $skipped === 0) {
            Log::warning('Marketing newsletter run matched no active subscribers.', [
                'run_id' => $this->runId,
                'send_to_all' => $this->sendToAll,
            ]);
            return;
        }

        Log::info('Marketing newsletter run completed.', [
            'run_id' => $this->runId,
            'sent' => $sent,
            'skipped_as_already_sent' => $skipped,
        ]);
    }

    private function queueMailForSubscriber(Subscriber $subscriber, array $posts): bool
    {
        try {
            NewsletterSend::create([
                'subscriber_id' => $subscriber->id,
                'run_id' => $this->runId,
                'sent_at' => now(),
            ]);
        } catch (UniqueConstraintViolationException) {
            return false;
        }

        try {
            Mail::to($subscriber->email)->queue(new BestPostsMail($posts));
        } catch (Throwable $e) {
            NewsletterSend::query()
                ->where('subscriber_id', $subscriber->id)
                ->where('run_id', $this->runId)
                ->delete();

            throw $e;
        }

        return true;
    }
}