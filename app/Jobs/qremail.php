<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\attendanceEmail;
use Mail;
class qremail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
public $user , $num;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user,$num)
    {
        $this->user = $user;
        $this->num = $num;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        if (filter_var($this->user->email, FILTER_VALIDATE_EMAIL)) {
            
            Mail::to($this->user->email)->send(new \App\Mail\attendanceEmail($this->num));
           
        
        }

    }
}
