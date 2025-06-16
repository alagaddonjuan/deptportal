<?php

namespace App\Notifications;

use App\Models\Assignment; // Import the Assignment model
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewAssignmentPosted extends Notification implements ShouldQueue // Optional: implements ShouldQueue for background sending
{
    use Queueable;

    public Assignment $assignment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Assignment $assignment)
    {
        $this->assignment = $assignment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // We'll store it in the database and also send an email (caught by Mailtrap)
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // The URL should ideally point to where the user can view this assignment
        // For now, a generic frontend URL or a placeholder
        $frontendUrl = config('app.frontend_url', config('app.url'));
        $assignmentUrl = $frontendUrl . '/assignments/' . $this->assignment->id; // Example URL

        return (new MailMessage)
                    ->subject('New Assignment Posted: ' . $this->assignment->title)
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line('A new assignment has been posted for the subject: ' . $this->assignment->subject->name)
                    ->line('Title: ' . $this->assignment->title)
                    ->line('Due Date: ' . ($this->assignment->due_date ? $this->assignment->due_date->format('Y-m-d H:i') : 'Not set'))
                    ->action('View Assignment', $assignmentUrl)
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'assignment_id' => $this->assignment->id,
            'assignment_title' => $this->assignment->title,
            'subject_name' => $this->assignment->subject->name, // Assumes subject relationship is loaded or accessible
            'message' => 'A new assignment "' . $this->assignment->title . '" has been posted for ' . $this->assignment->subject->name . '.',
            'url' => '/assignments/' . $this->assignment->id, // Relative URL for frontend routing
        ];
    }

    /**
     * Get the database representation of the notification. (Alternative to toArray for database channel)
     *
     * @param  object  $notifiable
     * @return array
     */
    // public function toDatabase(object $notifiable): array
    // {
    //     return [
    //         'assignment_id' => $this->assignment->id,
    //         'assignment_title' => $this->assignment->title,
    //         'subject_name' => $this->assignment->subject->name,
    //         'message' => 'A new assignment "' . $this->assignment->title . '" has been posted for ' . $this->assignment->subject->name . '.',
    //         'url' => '/assignments/' . $this->assignment->id,
    //     ];
    // }
}