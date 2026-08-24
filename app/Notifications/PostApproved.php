<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notify the author that their submitted post was approved and published.
 */
class PostApproved extends Notification
{
    public function __construct(public Post $post) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = url(route('blog.show', $this->post->slug, false));

        return (new MailMessage())
            ->subject('Your post has been published: ' . $this->post->title)
            ->greeting('Great news!')
            ->line('Your post was approved by an editor and is now live on Huvanti.')
            ->line('Title: ' . $this->post->title)
            ->action('View the published post', $url)
            ->line('Thanks for sharing your work with the community.');
    }
}
