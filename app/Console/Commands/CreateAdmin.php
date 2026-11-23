<?php

namespace App\Console\Commands;

use App\Enums\GlobalRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CreateAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-admin {first_name} {last_name} {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create an admin ';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $first_name = $this->argument('first_name');
        $last_name = $this->argument('last_name');
        $email = $this->argument('email');

        $validator = Validator::make(
            [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
            ],
            [
                'first_name' => ['required'],
                'last_name' => ['required'],
                'email' => ['required', 'email', 'max:254', Rule::unique('users', 'email')],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        // Give first password
        $password = $this->secret('gib password');
        $validator = Validator::make(
            ['password' => $password],
            ['password' => ['required', Password::min(6)]]
        );

        if ($validator->fails()) {
            $this->error($validator->errors()->all()[0]);
            return self::FAILURE;
        }

        // Confirm password
        $passwordConfirmation = $this->secret('Confirm password');
        $validator = Validator::make(
            ['password' => $password, 'password_confirmation' => $passwordConfirmation],
            ['password' => ['required', Password::min(6), 'confirmed']]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        User::create([
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'password' => $password,
            'global_role' => GlobalRole::Admin,
            'email_verified_at' => now(),
        ]);

        $this->info("User $first_name $last_name with email $email was created.");

        return self::SUCCESS;
    }
}
