<?php

namespace App\Modules\Auth\Actions;

use App\Models\User;

class CreateUserAction
{
    public function execute(array $data): User
    {
        return User::create($data);
    }
}
