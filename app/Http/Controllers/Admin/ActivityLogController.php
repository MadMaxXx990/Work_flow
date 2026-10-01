<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('employee')->where('is_deleted', false)->get();

        $query = ActivityLog::with('user.employee')->orderByDesc('created_at');

        if ($userId = $request->get('user_id'))       $query->where('user_id', $userId);
        if ($action = $request->get('action_type'))   $query->where('action_type', $action);
        if ($from   = $request->get('from'))          $query->whereDate('created_at', '>=', $from);
        if ($to     = $request->get('to'))            $query->whereDate('created_at', '<=', $to);
        if ($search = $request->get('search'))        $query->where('description', 'like', "%{$search}%");

        $logs        = $query->paginate(30)->withQueryString();
        $actionTypes = ActivityLog::distinct()->pluck('action_type')->filter()->sort()->values();

        return view('admin.activity-logs.index', compact('logs', 'users', 'actionTypes'));
    }
}
