<?php

namespace Modules\Profile\Services\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Profile\Models\Profile;

interface ProfileServiceInterface
{
    public function getByUserId(string $userId): ?Profile;
    public function ensureProfileExists(string $userId): Profile;
    public function updateProfile(string $userId, array $data): Profile;
    public function deleteAccount(string $userId): void;
}