<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExamCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $exam;
    public $user;
    public $examUrl;

    /**
     * Create a new message instance.
     */
    public function __construct($exam, $user, $examUrl)
    {
        $this->exam = $exam;
        $this->user = $user;
        $this->examUrl = $examUrl;
    }

    public function build(): static
    {
        $subject = 'Your Exam "' . $this->exam->title . '" has been created successfully!';

        return $this->subject($subject)
            ->markdown('emails.exam_created_mail')
            ->with([
                'exam' => $this->exam,
                'user' => $this->user,
                'examUrl' => $this->examUrl,
            ]);
    }
}
