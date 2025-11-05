<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FormSubmition extends Notification
{
    use Queueable;

    protected $submission;
    
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;
    
    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int[]
     */
    public $backoff = [5, 10, 15];

    /**
     * Create a new notification instance.
     */
    public function __construct($submission)
    {
        $this->submission = $submission;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail message.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $data = $this->submission;

        $mail = (new MailMessage)
            ->subject('New Form Submission Received')
            ->greeting('Hello!')
            ->line('A new form submission has been received. Here are the details:')
            ->line('**Full Name:** ' . ($data['full_name'] ?? 'N/A'))
            ->line('**Email:** ' . ($data['email'] ?? 'N/A'))
            ->line('**Phone Number:** ' . ($data['phone_number'] ? '[+'.$data['phone_number'].'](tel:'.preg_replace('/[^0-9+]/', '', $data['phone_number']).')' : 'N/A'))
            ->line('**Message:** ' . ($data['message'] ?? 'N/A'));

        // If services exist, format them as a list
        if (!empty($data['services'])) {
            $servicesList = collect($data['services'])->implode(', ');
            $mail->line('**Services:** ' . $servicesList);
        }

        return $mail
            ->line('Please review the submission and follow up as necessary.')
            ->salutation('Best regards,  
Your Website Notification System');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return $this->submission;
    }
}
