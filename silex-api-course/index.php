<?php
require 'vendor/autoload.php';
include 'bootstrap.php';

use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;

use Chatter\Models\Message;
use Chatter\Middleware\Logging as ChatterLogging;
use Chatter\Middleware\Authentication as ChatterAuth;

$filename = __DIR__.preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
if (php_sapi_name() === 'cli-server' && is_file($filename)) {
    return false;
}

$app = new Silex\Application();
$app->before(function($request, $app){
    ChatterLogging::log($request, $app);
    ChatterAuth::authenticate($request, $app);
});

$app->get('/messages', function() use ($app){
    $message = new Message();
    $messages = $message->all();

    $payload = [];
    foreach ($messages as $m) {
        $payload[$m->id] = [
            'body' => $m->body,
            'userId' => $m->user_id,
            'createdAt' => $m->created_at
        ];        
    }

    return json_encode($payload);
});

$app->post('/messages', function(Request $request) use ($app){
    $messageOnRequest = $request->get('message');
    $file = $_FILES['file'];
    $uploadFileName = $file['name'];

    move_uploaded_file($file['tmp_name'], "assets/images/$uploadFileName");
    $imageUrl = "assets/images/$uploadFileName";

    $message = new Message();
    $message->body = $messageOnRequest;
    $message->user_id = -1;
    $message->image_url = $imageUrl;
    $message->save();

    if($message->id)
    {
        $payload = [
            'message_id' => $message->id,
            'message_uri' => '/messages/'.$message->id
        ];
        $code = 201;
    }
    else
    {
        $payload = [];
        $code = 400;
    }

    return $app->json($payload, $code);
});

$app->delete('/messages/{message_id}', function($message_id) use ($app){
    $message = Message::find($message_id);
    $message->delete();

    if($message->exists)
    {
        return new Response('', 400);
    }
    else
    {
        return new Response('', 204);
    }
});

$app->run();