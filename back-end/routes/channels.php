<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('users.{id}', function ($user, $id) {
    return (string) $user->id === (string) $id;
});