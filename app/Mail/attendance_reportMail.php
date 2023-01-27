<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class attendance_reportMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $project , $attendance_report;
    public function __construct($project , $attendance_report)
    {
       
      $this->project =  $project; 
     
      $this->attendance_report =  $attendance_report;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('attendance_reportMail')->from('cc@portal-cp.com')->subject('attendance report on '.' '.$this->project->name);
    }
}
