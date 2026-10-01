<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index()
    {
        $positions = Position::withCount(['employees' => fn($q) => $q->where('is_deleted', false)])
            ->latest()
            ->get();

        return view('admin.positions.index', compact('positions'));
    }

    public function create()
    {
        return view('admin.positions.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'position_name' => 'required|string|max:255|unique:positions,position_name',
            'description'   => 'nullable|string|max:500',
        ]);

        $pos = Position::create($data);

        ActivityLogService::log('create', "Created position: {$pos->position_name}", 'positions', $pos->position_id);

        return redirect()->route('admin.positions.index')
            ->with('success', "Position \"{$pos->position_name}\" created.");
    }

    public function edit(Position $position)
    {
        return view('admin.positions.edit', compact('position'));
    }

    public function update(Request $request, Position $position)
    {
        $data = $request->validate([
            'position_name' => "required|string|max:255|unique:positions,position_name,{$position->position_id},position_id",
            'description'   => 'nullable|string|max:500',
        ]);

        $position->update($data);

        ActivityLogService::log('update', "Updated position: {$position->position_name}", 'positions', $position->position_id);

        return redirect()->route('admin.positions.index')
            ->with('success', "Position \"{$position->position_name}\" updated.");
    }

    public function destroy(Position $position)
    {
        if ($position->employees()->where('is_deleted', false)->exists()) {
            return back()->with('error', 'Cannot remove a position that still has active employees.');
        }

        $position->delete();

        ActivityLogService::log('delete', "Deleted position: {$position->position_name}", 'positions', $position->position_id);

        return redirect()->route('admin.positions.index')
            ->with('success', "Position \"{$position->position_name}\" removed.");
    }
}
