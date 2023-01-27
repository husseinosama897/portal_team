<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ccEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

  public $content , $contentmanager;
    public function __construct($content,$contentmanager)
    {
        $this->content = $content;
        $this->contentmanager = $contentmanager;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
       
        return $this->markdown('email')->from('cc@portal-cp.com')->subject('new notification');
    }
}
