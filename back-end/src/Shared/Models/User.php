<?php

namespace Shared\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Shared\Traits\HasUuid;

class User extends Authenticatable
{
    use HasUuid;

    // Shared model attributes: nothing else, only truly common behaviour.
    // Identity module will extend this.
}