<?php

namespace Modules\Marketing\Services;

use Modules\Marketing\Models\ContactMessage;
use Modules\Marketing\DTOs\ContactMessageDTO;
use Modules\Marketing\Actions\SendContactEmailAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Mail;
use Modules\Marketing\Mail\AdminReplyMail;

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

    public function replyToMessage(ContactMessage $message, string $replyBody): void
    {
        Mail::to($message->email)->queue(new AdminReplyMail($message, $replyBody));
        $this->markAsRead($message); // Auto-mark as read when replied
    }

    public function toggleReadStatus(ContactMessage $message): void
    {
        $message->update(['is_read' => !$message->is_read]);
    }
}