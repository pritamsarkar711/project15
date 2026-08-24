<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notify admins that an author submitted a post for review.
 *
 * Sent to every admin (role='admin') when an author hits "Submit for review"
 * in the author dashboard. The email includes a direct link to the admin's
 * review-queue page so the admin can act in one click.
 */
class PostSubmittedForReview extends Notification
{
    public function __construct(public Post $post) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $author = $this->post->author_name ?: ($this->post->user?->name ?: 'An author');

        return (new MailMessage())
            ->subject('New post submitted for review: ' . $this->post->title)
            ->greeting('New submission on Huvanti')
            ->line("{$author} submitted a post for review.")
            ->line('Title: ' . $this->post->title)
            ->line('Submitted: ' . $this->post->submitted_at?->format('M d, Y H:i'))
            ->action('Open Review Queue', url(route('admin.posts.review-queue', ['tab' => 'pending'], false)))
            ->line('You can edit, approve, or return the post from the review queue.');
    }
}
