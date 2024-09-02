<?php
namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Broadcast::routes(['middleware' => ['auth:sanctum']]);

        Broadcast::channel('channel.{channelId}', function ($user, $channelId) {
            return $user->channels->contains($channelId);
        });
    }
}
