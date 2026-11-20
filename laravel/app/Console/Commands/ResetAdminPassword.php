<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;

/**
 * Password recovery without e-mail: run over SSH by someone with server access.
 * Signs the account out everywhere by rotating its remember token and sessions.
 */
#[Signature('cms:reset-password {email}')]
#[Description('Set a new password for an administrator (signs them out everywhere)')]
class ResetAdminPassword extends Command
{
    public function handle(): int
    {
        $user = User::query()->where('email', mb_strtolower($this->argument('email')))->first();
        if (! $user) {
            $this->error('No administrator with that email address.');

            return self::FAILURE;
        }
        $pass = password('New password (at least 12 characters, letters and numbers)', required: true);
        $confirm = password('Repeat the new password', required: true);
        $validator = Validator::make(['password' => $pass, 'password_confirmation' => $confirm], ['password' => ['required', 'confirmed', Password::defaults()]]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }
        $user->forceFill(['password' => $pass, 'remember_token' => null, 'is_active' => true])->save();
        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
        }
        $this->info('Password updated. The administrator has been signed out of all devices.');

        return self::SUCCESS;
    }
}
