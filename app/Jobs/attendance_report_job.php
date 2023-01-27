<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\attendance_reportMail;
use Mail;
class attendance_report_job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $project , $attendance_report;
    public function __construct($project,$attendance_report)
    {
        $this->project = $project;
        
        $this->attendance_report = $attendance_report;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (filter_var($this->project->projectmanager->email, FILTER_VALIDATE_EMAIL)) {
            
            Mail::to($this->project->projectmanager->email)->send(new \App\Mail\attendance_reportMail($this->project,$this->attendance_report));
           
        
        }
    }
}
