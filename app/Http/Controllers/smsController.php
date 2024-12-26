<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;

class smsController extends Controller
{
    public function sendSms()
    {
        $sid    = getenv("TWILIO_SID");
        $token  = getenv("TWILIO_TOKEN");
        $senderNumber = getenv("TWILIO_PHONE");
        $twilio = new Client($sid, $token);
    
        $message = $twilio->messages
          ->create("+8801825647693", // to
            array(
              "from" => $senderNumber,
              "body" => ["Hello from Twilio"]
            )
          );
          dd("message send successfully");
    }
}
