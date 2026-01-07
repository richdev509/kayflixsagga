<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Creator;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreatorUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer l'utilisateur créateur
        $creatorUser = User::firstOrCreate(
            ['email' => 'creator@stream.com'],
            [
                'name' => 'Créateur Test',
                'password' => Hash::make('creator123'),
            ]
        );

        // S'assurer que le rôle creator existe
        $creatorRole = Role::firstOrCreate(['name' => 'creator', 'guard_name' => 'web']);

        // Assigner le rôle creator
        if (!$creatorUser->hasRole('creator')) {
            $creatorUser->assignRole('creator');
        }

        // Créer l'entrée creator
        Creator::firstOrCreate(
            ['user_id' => $creatorUser->id],
            [
                'bio' => 'Je suis un créateur de contenu passionné par la technologie et l\'éducation.',
                'status' => 'approved',
                'channel_name' => 'Tech Creator Channel',
            ]
        );

        $this->command->info('Creator user created successfully!');
        $this->command->info('Email: creator@stream.com');
        $this->command->info('Password: creator123');
    }
}
