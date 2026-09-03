<?php

use Modules\Marketing\Services\NewsletterService;
use Modules\Marketing\Models\Subscriber;
use Modules\Marketing\DTOs\SubscribeDTO;
use Modules\Marketing\Exceptions\MarketingException;
use Modules\Marketing\Jobs\SendBestPostsNewsletterJob;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    $this->service = new NewsletterService(
        new \Modules\Marketing\Actions\SubscribeToNewsletterAction()
    );
});

test('subscribe creates new subscriber', function () {
    $dto = new SubscribeDTO(email: 'new@example.com');
    $subscriber = $this->service->subscribe($dto);

    expect($subscriber)->toBeInstanceOf(Subscriber::class);
    $this->assertDatabaseHas('marketing_subscribers', ['email' => 'new@example.com']);
});

test('subscribe reactivates inactive subscriber', function () {
    $existing = Subscriber::factory()->inactive()->create(['email' => 'test@example.com']);
    $dto = new SubscribeDTO(email: 'test@example.com');

    $subscriber = $this->service->subscribe($dto);
    expect($subscriber->id)->toBe($existing->id);
    expect($subscriber->is_active)->toBeTrue();
});

test('subscribe throws alreadySubscribed for active subscriber', function () {
    Subscriber::factory()->create(['email' => 'active@example.com', 'is_active' => true]);
    $dto = new SubscribeDTO(email: 'active@example.com');

    expect(fn () => $this->service->subscribe($dto))
        ->toThrow(MarketingException::class);
});

test('getAdminSubscribers returns paginated results with max limit', function () {
    Subscriber::factory()->count(5)->create();

    $paginator = $this->service->getAdminSubscribers(perPage: 2);
    expect($paginator)->toHaveCount(2);
    expect($paginator->total())->toBe(5);
    expect($paginator->perPage())->toBe(2);
});

test('getAdminSubscribers clamps perPage to max 100', function () {
    Subscriber::factory()->count(3)->create();
    $paginator = $this->service->getAdminSubscribers(perPage: 1000);
    expect($paginator->perPage())->toBe(100);
});

test('dispatchNewsletterJob with subscriber ids', function () {
    $subscriberIds = [Subscriber::factory()->create()->id];
    $this->service->dispatchNewsletterJob(subscriberIds: $subscriberIds, sendToAll: false);

    Queue::assertPushed(SendBestPostsNewsletterJob::class, function ($job) use ($subscriberIds) {
        return $job->subscriberIds === $subscriberIds && $job->sendToAll === false;
    });
});

test('dispatchNewsletterJob with sendToAll', function () {
    $this->service->dispatchNewsletterJob(subscriberIds: null, sendToAll: true);

    Queue::assertPushed(SendBestPostsNewsletterJob::class, function ($job) {
        return $job->sendToAll === true;
    });
});

test('dispatchNewsletterJob throws if no subscribers and not sendToAll', function () {
    expect(fn () => $this->service->dispatchNewsletterJob(subscriberIds: [], sendToAll: false))
        ->toThrow(MarketingException::class);
});

test('deleteSubscriber deletes the subscriber', function () {
    $subscriber = Subscriber::factory()->create();
    $this->service->deleteSubscriber($subscriber);

    $this->assertDatabaseMissing('marketing_subscribers', ['id' => $subscriber->id]);
});

test('toggleStatus toggles is_active', function () {
    $subscriber = Subscriber::factory()->create(['is_active' => true]);
    $this->service->toggleStatus($subscriber);
    expect($subscriber->fresh()->is_active)->toBeFalse();

    $this->service->toggleStatus($subscriber);
    expect($subscriber->fresh()->is_active)->toBeTrue();
});