<?php
use App\Models\User;

function getUser($param){
    $user = User::where('id', $param)
                ->orWhere('email', $param)
                ->orWhere('username', $param)
                ->first();

    $user->profile_picture = $user->profile_picture ? url('storage/'.$user->profile_picture) : "";

    return $user;

}
