<?php
namespace Chatter\Middleware;

use Chatter\Models\User;

class Authentication 
{
    public static function authenticate($request, $app)
    {
        $auth = $request->headers->get('Authorization');
        $apiKey = substr($auth, strpos($auth, ' '));
        $apiKey = trim($apiKey);

        $user = new User();
        if(!$user->authenticate($apiKey))
        {
            $app->abort(401);
        }
    }
}