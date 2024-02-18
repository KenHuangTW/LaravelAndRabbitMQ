<?php

namespace App\Http\Controllers;

use App\Mail\HelloWorldMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

class MailController extends Controller
{
    public function sendMail(Request $request)
    {
        $target_mail = $request->all('mail_address');

        // 推進 queue 裡面
        Queue::push(function($job) use ($target_mail) {
            Mail::to($target_mail)->send(new HelloWorldMail());
            $job->delete();
        });

        return "Email has been sent successfully!";
    }
}
