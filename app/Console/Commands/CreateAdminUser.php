<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create {email} {password} {--name=Admin}';

    protected $description = 'Buat akun admin baru untuk login ke /admin';

    public function handle(): int
    {
        $validator = Validator::make([
            'email'    => $this->argument('email'),
            'password' => $this->argument('password'),
        ], [
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        User::create([
            'name'     => $this->option('name'),
            'email'    => $this->argument('email'),
            'password' => $this->argument('password'),
        ]);

        $this->info("Admin berhasil dibuat: {$this->argument('email')}");

        return self::SUCCESS;
    }
}
