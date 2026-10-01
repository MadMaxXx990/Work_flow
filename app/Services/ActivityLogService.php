<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogService
{
    /**
     * Record a user action to the activity_logs table.
     */
    public static function log(
        string $actionType,
        string $description,
        ?string $tableAffected = null,
        ?int $recordId = null,
        ?int $userId = null
    ): void {
        ActivityLog::create([
            'user_id'       => $userId ?? Auth::id(),
            'action_type'   => $actionType,
            'table_affected'=> $tableAffected,
            'record_id'     => $recordId,
            'description'   => $description,
        ]);
    }
}
