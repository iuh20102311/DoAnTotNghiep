<?php

use Illuminate\Support\Facades\Broadcast;

//Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//    return (int) $user->id === (int) $id;
//});

Broadcast::channel('channel.{channelId}', function ($user, $channelId) {
    return $user->channels->contains($channelId);
});


