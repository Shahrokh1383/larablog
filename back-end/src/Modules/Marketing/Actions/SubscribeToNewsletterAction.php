<?php

namespace Modules\Marketing\Actions;

use Illuminate\Database\UniqueConstraintViolationException;
use Modules\Marketing\Models\Subscriber;
use Modules\Marketing\DTOs\SubscribeDTO;
use Modules\Marketing\Exceptions\MarketingException;

class SubscribeToNewsletterAction
{
    public function execute(SubscribeDTO $dto): Subscriber
    {
        $existing = Subscriber::where('email', $dto->email)->first();

        if ($existing) {
            if ($existing->is_active) {
                throw MarketingException::alreadySubscribed();
            }
            // Reactivate if previously unsubscribed
            $existing->update(['is_active' => true]);
            return $existing;
        }

        try {
            return Subscriber::create(['email' => $dto->email]);
        } catch (UniqueConstraintViolationException) {
            throw MarketingException::alreadySubscribed();
        }
    }
}