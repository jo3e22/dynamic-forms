<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Form;
use App\Models\Submission;

class NotificationService
{
    /**
     * Create a new submission notification
     */
    public function notifyNewSubmission(User $formOwner, Form $form, Submission $submission): Notification
    {
        return Notification::create([
            'user_id' => $formOwner->id,
            'type' => Notification::TYPE_NEW_SUBMISSION,
            'title' => 'New Form Submission',
            'message' => "New submission received for '{$form->title}'",
            'data' => [
                'form_id' => $form->id,
                'form_code' => $form->code,
                'submission_id' => $submission->id,
                'submission_code' => $submission->code,
                'link' => "/forms/{$form->code}/submissions/{$submission->code}",
            ],
        ]);
    }

    /**
     * Create a form published notification
     */
    public function notifyFormPublished(User $user, Form $form): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'type' => Notification::TYPE_FORM_PUBLISHED,
            'title' => 'Form Published',
            'message' => "Your form '{$form->title}' has been published",
            'data' => [
                'form_id' => $form->id,
                'form_code' => $form->code,
                'link' => "/forms/{$form->code}",
            ],
        ]);
    }

    /**
     * Create a collaboration invite notification
     */
    public function notifyCollaborationInvite(User $invitedUser, User $inviter, Form $form): Notification
    {
        return Notification::create([
            'user_id' => $invitedUser->id,
            'type' => Notification::TYPE_COLLABORATION_INVITE,
            'title' => 'Collaboration Invite',
            'message' => "{$inviter->name} invited you to collaborate on '{$form->title}'",
            'data' => [
                'form_id' => $form->id,
                'form_code' => $form->code,
                'inviter_id' => $inviter->id,
                'inviter_name' => $inviter->name,
                'link' => "/forms/{$form->code}/edit",
            ],
        ]);
    }

    /**
     * Get unread count for user
     */
    public function getUnreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification): void
    {
        $notification->markAsRead();
    }

    /**
     * Mark all user notifications as read
     */
    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications()->update([
            'read' => true,
            'read_at' => now(),
        ]);
    }
}