<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notify the author that their submitted post was returned for revision.
 * Includes the reviewer's note explaining what to change.
 */
class PostReturned extends Notification
{
    public function __construct(public Post $post) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $editUrl = url(route('author.posts.edit', $this->post->id, false));

        $msg = (new MailMessage())
            ->subject('Action needed: ' . $this->post->title)
            ->greeting('Your post needs a little more work')
            ->line('An editor reviewed "' . $this->post->title . '" and returned it for revision.')
            ->line('Editor\'s note:')
            ->line($this->post->reviewer_note ?: 'No note was provided. Please review the post and re-submit.')
            ->action('Edit your post', $editUrl);

        return $msg;
    }
}
