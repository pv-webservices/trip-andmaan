<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

/**
 * Creates an administrator from the server shell. The password is typed
 * interactively (never passed on the command line, so it stays out of shell
 * history) and must meet the password rules.
 */
#[Signature('cms:create-admin {--email=} {--name=}')]
#[Description('Create an administrator account (interactive; there is no public registration)')]
class CreateAdmin extends Command
{
    public function handle(): int
    {
        $email = $this->option('email') ?: text('Email address', required: true);
        $name = $this->option('name') ?: text('Name', required: true);
        $pass = password('Password (at least 12 characters, letters and numbers)', required: true);
        $confirm = password('Repeat the password', required: true);

        $validator = Validator::make(
            ['email' => $email, 'name' => $name, 'password' => $pass, 'password_confirmation' => $confirm],
            ['email' => ['required', 'email', 'max:190', 'unique:users,email'], 'name' => ['required', 'max:120'], 'password' => ['required', 'confirmed', Password::defaults()]],
        );
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        User::query()->create(['name' => $name, 'email' => mb_strtolower($email), 'password' => $pass, 'is_active' => true]);
        $this->info("Administrator {$email} created. Sign in at /admin.");

        return self::SUCCESS;
    }
}
