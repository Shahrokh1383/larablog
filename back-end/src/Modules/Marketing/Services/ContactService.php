<?php

namespace Modules\Marketing\Services;

use Modules\Marketing\Models\ContactMessage;
use Modules\Marketing\DTOs\ContactMessageDTO;
use Modules\Marketing\Actions\SendContactEmailAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ContactService
{
    public function __construct(
        private readonly SendContactEmailAction $sendAction
    ) {}

    public function submitMessage(ContactMessageDTO $dto): ContactMessage
    {
        return $this->sendAction->execute($dto);
    }

    public function getAdminMessages(int $perPage = 20): LengthAwarePaginator
    {
        return ContactMessage::with('user')->latest()->paginate($perPage);
    }

    public function markAsRead(ContactMessage $message): void
    {
        if (!$message->is_read) {
            $message->update(['is_read' => true]);
        }
    }

    public function deleteMessage(ContactMessage $message): void
    {
        $message->delete();
    }
}