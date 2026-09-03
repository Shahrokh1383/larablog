<?php

use Modules\Marketing\Models\Subscriber;
use Modules\Marketing\Http\Resources\SubscriberResource;
use Illuminate\Http\Request;

test('SubscriberResource transforms model correctly', function () {
    $subscriber = Subscriber::factory()->create([
        'email' => 'test@example.com',
        'is_active' => true,
    ]);

    $resource = new SubscriberResource($subscriber);
    $array = $resource->toArray(new Request());

    expect($array)->toHaveKeys(['id', 'email', 'is_active', 'created_at']);
    expect($array['id'])->toBe($subscriber->id);
    expect($array['email'])->toBe('test@example.com');
    expect($array['is_active'])->toBeTrue();
    expect($array['created_at'])->toBe($subscriber->created_at->toISOString());
});