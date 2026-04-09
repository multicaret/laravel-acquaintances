<?php

use App\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Create a user
 *
 * @param  array  $overrides
 * @param  int  $amount
 * @return Collection|User[]|User
 */
if (! function_exists('createUser')) {
    function createUser($overrides = [], $amount = 1)
    {
        $users = collect();

        for ($i = 0; $i < $amount; $i++) {
            $users->push(User::create(array_merge([
                'name' => 'User '.uniqid(),
                'email' => uniqid().'@example.com',
                'password' => bcrypt('password'),
            ], $overrides)));
        }

        if ($users->count() === 1) {
            return $users->first();
        }

        return $users;
    }
}
