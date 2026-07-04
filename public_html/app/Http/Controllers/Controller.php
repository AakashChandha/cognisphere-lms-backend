<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function sendEmail(String $toEmail, String $subject, $mdata, $layout="email.email-verification-code") {

         Mail::send($layout, $mdata, function ($message) use ($toEmail, $subject) {
             $message->from('support@cognispheremc.com', 'Cognisphere');
             $message->to($toEmail, '')
                 ->subject($subject);
         });
 
     }
}
