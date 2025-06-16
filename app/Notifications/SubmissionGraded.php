<?php

namespace App\Notifications;

use App\Models\Submission; // Import the Submission model
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubmissionGraded extends Notification implements ShouldQueue // Optional: implements ShouldQueue
{
    use Queueable;

    public Submission $submission;

    /**
     * Create a new notification instance.
     */
    public function __construct(Submission $submission)
    {
        $this->submission = $submission;
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
        // Assuming your frontend has a route to view a specific submission or assignment results
        // Adjust this path as per your frontend routing
        $submissionViewPath = '/my-submissions'; // Or perhaps /assignments/{assignment_id}/my-submission
        $submissionUrl = $frontendUrl . $submissionViewPath . '?assignment_id=' . $this->submission->assignment_id;


        return (new MailMessage)
                    ->subject('Your Submission has been Graded: ' . $this->submission->assignment->title)
                    ->greeting('Hello ' . $notifiable->name . ',') // $notifiable will be the student User model
                    ->line('Your submission for the assignment "' . $this->submission->assignment->title . '" (Subject: ' . $this->submission->assignment->subject->name . ') has been graded.')
                    ->line('Marks Awarded: ' . $this->submission->marks_awarded . ' / ' . $this->submission->assignment->max_marks)
                    ->line('Feedback: ' . ($this->submission->feedback ?: 'No feedback provided.'))
                    ->action('View Submission Details', $submissionUrl)
                    ->line('Thank you!');
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray(object $notifiable): array
    {
        // Ensure necessary relationships are loaded to prevent errors
        $this->submission->loadMissing(['assignment.subject:id,name', 'assignment:id,title,max_marks']);

        return [
            'submission_id' => $this->submission->id,
            'assignment_id' => $this->submission->assignment->id,
            'assignment_title' => $this->submission->assignment->title,
            'subject_name' => $this->submission->assignment->subject->name,
            'marks_awarded' => $this->submission->marks_awarded,
            'max_marks' => $this->submission->assignment->max_marks,
            'message' => 'Your submission for "' . $this->submission->assignment->title . '" has been graded. Marks: ' . $this->submission->marks_awarded . '/' . $this->submission->assignment->max_marks,
            'url' => '/my-submissions?assignment_id=' . $this->submission->assignment_id, // Relative URL for frontend
            'action_text' => 'View Grade',
        ];
    }
}