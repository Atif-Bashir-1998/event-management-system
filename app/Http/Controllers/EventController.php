<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Constants\Event\Constants as EventConstants;
use App\Constants\RolePermission\Constants as RolePermissionConstants;
use Illuminate\Support\Facades\Log;

class EventController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view_event', only: ['index', 'show']),
            new Middleware('can:create_event', only: ['store', 'create']),
            new Middleware('can:update_event', only: ['update']),
            new Middleware('can:delete_event', only: ['destroy']),
        ];
    }

    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        return Inertia::render('Event/Index', [
            'events' => $user->get_visible_events(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Event/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'event_type' => 'required|in:' . implode(',', EventConstants::EVENT_TYPES), // Event type should be one of the defined constants
            'description' => 'required|string',
            'image_url' => 'nullable|url',
            'capacity_limit' => 'required|integer|min:1',
            'waiting_list_size' => 'nullable|integer|min:0',
            'automatic_ticket_upgrade' => 'required|boolean',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:' . implode(',', EventConstants::EVENT_STATUSES),
            'cancellation_policy' => 'nullable|string',
            'created_by' => 'required|exists:users,id',
        ]);

        Event::create($validated);

        return back()->with('success', 'Event has been created');
    }

    public function show(Event $event)
    {
        //
    }

    public function edit(Event $event)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($event->created_by != $user->id && !$event->organizers->contains($user)) {
            abort(403, 'You are not authorized to edit this event.');
        }

        return Inertia::render('Event/Edit', [
            'event' => $event
        ]);
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'event_type' => 'required|in:' . implode(',', EventConstants::EVENT_TYPES), // Event type should be one of the defined constants
            'description' => 'required|string',
            'image_url' => 'nullable|url',
            'capacity_limit' => 'required|integer|min:1',
            'waiting_list_size' => 'nullable|integer|min:0',
            'automatic_ticket_upgrade' => 'required|boolean',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:' . implode(',', EventConstants::EVENT_STATUSES),
            'cancellation_policy' => 'nullable|string',
        ]);

        /** @var User $user */
        $user = Auth::user();

        if (
            !$user->hasPermissionTo("update_event") &&
            ($event->created_by != $user->id || !$event->organizers->contains($user))
        ) {
            abort(403, 'You are not authorized to update this event.');
        }

        $event->update($validated);

        return back()->with('success', 'Event has been updated');
    }

    public function destroy(Event $event)
    {
        //
    }
}
