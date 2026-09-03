<?php

namespace Modules\Marketing\Services;

use Modules\Marketing\Models\ContactMessage;
use Modules\Marketing\DTOs\ContactMessageDTO;
use Modules\Marketing\Actions\SendContactEmailAction;
use Modules\Marketing\Exceptions\MarketingException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Mail;
use Modules\Marketing\Mail\AdminReplyMail;
use Throwable;

class ContactService
{
    private const MAX_PER_PAGE = 100;

    public function __construct(
        private readonly SendContactEmailAction $sendAction
    ) {}

    public function submitMessage(ContactMessageDTO $dto): ContactMessage
    {
        return $this->sendAction->execute($dto);
    }

    public function getAdminMessages(int $perPage = 20): LengthAwarePaginator
    {
        return ContactMessage::with('user')->latest()->paginate(
            max(1, min(self::MAX_PER_PAGE, $perPage))
        );
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

    public function replyToMessage(ContactMessage $message, string $replyBody): void
    {
        $claimed = ContactMessage::query()
            ->whereKey($message->getKey())
            ->whereNull('replied_at')
            ->update(['replied_at' => now()]);

        if ($claimed === 0) {
            throw MarketingException::alreadyReplied();
        }

        try {
            Mail::to($message->email)->queue(new AdminReplyMail($message, $replyBody));
        } catch (Throwable $e) {
            ContactMessage::query()
                ->whereKey($message->getKey())
                ->update(['replied_at' => null]);

            throw $e;
        }

        $this->markAsRead($message);
    }

    public function toggleReadStatus(ContactMessage $message): void
    {
        $message->update(['is_read' => !$message->is_read]);
    }
}