<?php

use Modules\Marketing\Models\Subscriber;
use Modules\Marketing\Actions\SubscribeToNewsletterAction;
use Modules\Marketing\DTOs\SubscribeDTO;
use Modules\Marketing\Exceptions\MarketingException;

beforeEach(function () {
    $this->action = new SubscribeToNewsletterAction();
});

test('new subscriber is created', function () {
    $dto = new SubscribeDTO(email: 'new@example.com');
    $subscriber = $this->action->execute($dto);

    expect($subscriber)->toBeInstanceOf(Subscriber::class);
    expect($subscriber->email)->toBe('new@example.com');
    $subscriber->refresh();
    expect($subscriber->is_active)->toBeTrue();
    $this->assertDatabaseHas('marketing_subscribers', ['email' => 'new@example.com', 'is_active' => true]);
});

test('existing active subscriber throws alreadySubscribed', function () {
    Subscriber::factory()->create(['email' => 'existing@example.com', 'is_active' => true]);
    $dto = new SubscribeDTO(email: 'existing@example.com');

    expect(fn () => $this->action->execute($dto))
        ->toThrow(MarketingException::class, 'This email is already subscribed to our newsletter.');
});

test('existing inactive subscriber is reactivated', function () {
    $subscriber = Subscriber::factory()->inactive()->create(['email' => 'inactive@example.com']);
    $dto = new SubscribeDTO(email: 'inactive@example.com');

    $returned = $this->action->execute($dto);
    expect($returned->id)->toBe($subscriber->id);
    expect($returned->is_active)->toBeTrue();
    $this->assertDatabaseHas('marketing_subscribers', [
        'id' => $subscriber->id,
        'is_active' => true,
    ]);
});

test('unique constraint violation is handled', function () {
    $this->expectException(MarketingException::class);
    $dto = new SubscribeDTO(email: 'race@example.com');
    Subscriber::create(['email' => $dto->email]);
    $this->action->execute($dto);
});