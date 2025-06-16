<?php

namespace App\Notifications;

use App\Models\Announcement; // Import the Announcement model
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewCourseAnnouncement extends Notification implements ShouldQueue // Optional: implements ShouldQueue
{
    use Queueable;

    public Announcement $announcement;

    /**
     * Create a new notification instance.
     */
    public function __construct(Announcement $announcement)
    {
        $this->announcement = $announcement;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail']; // Store in DB and send email
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        $frontendUrl = config('app.frontend_url', config('app.url'));
        // The URL should point to where the user can view announcements
        $announcementUrl = $frontendUrl . '/announcements/' . $this->announcement->id; // Example URL

        return (new MailMessage)
                    ->subject('New Announcement: ' . $this->announcement->title)
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line('A new announcement has been posted for one of your courses.')
                    ->line('Title: ' . $this->announcement->title)
                    ->action('View Announcement', $announcementUrl)
                    ->line('Please log in to your portal to see the full details.');
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray(object $notifiable): array
    {
        return [
            'announcement_id' => $this->announcement->id,
            'announcement_title' => $this->announcement->title,
            'message' => 'A new announcement has been posted: "' . $this->announcement->title . '".',
            'url' => '/announcements/' . $this->announcement->id, // Relative URL for frontend routing
            'action_text' => 'View Announcement',
        ];
    }
}