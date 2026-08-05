<?php

namespace Modules\Profile\Services\Contracts;

use Modules\Profile\DTOs\UpdateProfileDTO;
use Modules\Profile\Models\Profile;

interface ProfileServiceInterface
{
    public function getByUserId(string $userId): ?Profile;
    public function updateProfile(string $userId, UpdateProfileDTO $dto): Profile;
    public function getPublicProfileByUsername(string $username): ?Profile;
    public function getAllPublicProfiles();
}