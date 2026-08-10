<?php

namespace Modules\Marketing\Policies;

use Shared\Models\User;
use Illuminate\Database\Eloquent\Model;

class MarketingPolicy
{
    public function viewAny(User $user): bool { return $user->hasRole('admin'); }
    public function view(User $user, Model $model): bool { return $user->hasRole('admin'); }
    public function create(User $user): bool { return $user->hasRole('admin'); }
    public function update(User $user, Model $model): bool { return $user->hasRole('admin'); }
    public function delete(User $user, Model $model): bool { return $user->hasRole('admin'); }
}