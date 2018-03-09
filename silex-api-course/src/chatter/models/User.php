<?php
namespace Chatter\Models;

class User extends \Illuminate\Database\Eloquent\Model
{
    public function authenticate($apiKey)
    {
        $user = User::where('apikey', '=', $apiKey)
                        ->take(1)
                        ->get();
        
        return ($user->count() == 1) ? true : false;
    }   
}