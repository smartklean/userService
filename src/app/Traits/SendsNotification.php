<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;

trait SendsNotification
{
  public function sendNotification($template, $recipients = [], $data = [], $channels = ['email']){
    return Http::withHeaders([
      'Content-Type' => 'application/json'
    ])->post(config('notificationsws.url').'/send', [
      'templateName' => $template,
      'recipients' => $recipients,
      'templateData' => $data,
      'channels' => $channels
    ]);
  }
}
