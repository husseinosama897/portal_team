<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use \App\Mail\ccEmail;
use Illuminate\Support\Facades\Mail;
class rolecc implements ShouldQueue
{

    protected $user, $content,$managercontent;


    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
      public function __construct($user,$content,$managercontent)
    {
        $this->user = $user;
        $this->content = $content;
        $this->managercontent = $managercontent;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        if (filter_var($this->user->email, FILTER_VALIDATE_EMAIL)) {

        Mail::to($this->user->email)->send(new \App\Mail\ccEmail($this->content,$this->managercontent));


    }

    }
}
