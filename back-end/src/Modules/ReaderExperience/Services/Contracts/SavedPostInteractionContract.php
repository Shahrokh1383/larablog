<?php

namespace Modules\ReaderExperience\Services\Contracts;

interface SavedPostInteractionContract
{
    /**
     * Get the IDs of posts saved by a user from a given list of post IDs.
     *
     * @param string $userId
     * @param array $postIds
     * @return array
     */
    public function getSavedPostIdsForUser(string $userId, array $postIds): array;
}