<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Channel;

class ChannelSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();

        $channel1 = Channel::create(['name' => 'General', 'is_private' => false]);
        $channel2 = Channel::create(['name' => 'Random', 'is_private' => false]);

        foreach ($users as $user) {
            $channel1->users()->attach($user->id);
            $channel2->users()->attach($user->id);
        }

        // Create some private channels
        for ($i = 0; $i < 3; $i++) {
            $privateChannel = Channel::create([
                'name' => 'Private ' . ($i + 1),
                'is_private' => true
            ]);
            $privateChannel->users()->attach($users->random(2)->pluck('id'));
        }
    }
}
