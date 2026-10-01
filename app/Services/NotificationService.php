<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    /**
     * Send a notification to one or more user IDs.
     *
     * @param  int|array  $userIds
     */
    public static function send(
        int|array $userIds,
        string $type,
        string $title,
        string $message,
        ?int $referenceId = null,
        ?int $senderUserId = null
    ): void {
        $userIds  = is_array($userIds) ? $userIds : [$userIds];
        $senderId = $senderUserId ?? Auth::id();

        $records = [];
        $now     = now();

        foreach ($userIds as $uid) {
            $records[] = [
                'user_id'           => $uid,
                'sender_user_id'    => $senderId,
                'notification_type' => $type,
                'reference_id'      => $referenceId,
                'title'             => $title,
                'message'           => $message,
                'is_read'           => false,
                'created_at'        => $now,
                'updated_at'        => $now,
            ];
        }

        Notification::insert($records);
    }
}
