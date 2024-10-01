<?php

namespace Tests\Unit\Event;

use App\Constants\RolePermission\Constants as RolePermissionConstants;
use App\Constants\Event\Constants as EventConstants;
use App\Models\Event;
use App\Models\User;
use App\Policies\Event\EventPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EventPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected $policy;

    protected $admin_user;
    protected $organizer_user;
    protected $attendee_user;

    public function setUp(): void
    {
        parent::setUp();

        Permission::create(['name' => 'view_event']);
        Permission::create(['name' => 'create_event']);
        Permission::create(['name' => 'update_event']);
        Permission::create(['name' => 'delete_event']);

        $admin_role = Role::create(['name' => RolePermissionConstants::DEFAULT_ROLES['ADMIN']]);
        $admin_role->syncPermissions(Permission::all());

        $organizer_role = Role::create(['name' => RolePermissionConstants::DEFAULT_ROLES['ORGANIZER']]);
        $organizer_role->syncPermissions(['update_event']);

        $attendee_role = Role::create(['name' => RolePermissionConstants::DEFAULT_ROLES['ATTENDEE']]);

        // create users with roles
        $this->admin_user = User::factory()->create()->assignRole($admin_role);
        $this->organizer_user = User::factory()->create()->assignRole($organizer_role);
        $this->attendee_user = User::factory()->create()->assignRole($attendee_role);


        $this->policy = new EventPolicy;
    }

    public function test_admin_can_view_any_event(): void
    {
        foreach (EventConstants::EVENT_STATUSES as $status) {
            // Create event by other user (organizer)
            $event_by_other_user = Event::factory()->create([
                'created_by' => $this->organizer_user->id,
                'status' => $status
            ]);

            // Create event by self (admin user)
            $event_by_self = Event::factory()->create([
                'created_by' => $this->admin_user->id,
                'status' => $status
            ]);

            // Test conditions
            $this->assertTrue(
                $this->policy->view($this->admin_user, $event_by_other_user)
            );
            $this->assertTrue(
                $this->policy->view($this->admin_user, $event_by_self),
            );
        }
    }

    public function test_organizer_can_view_published_cancelled_and_their_own_draft_events(): void
    {
        // Published and Cancelled Events by another user
        $published_event_by_other_users = Event::factory()->create([
            'created_by' => $this->admin_user->id,
            'status' => EventConstants::EVENT_STATUSES['PUBLISHED']
        ]);

        $cancelled_event_by_other_users = Event::factory()->create([
            'created_by' => $this->admin_user->id,
            'status' => EventConstants::EVENT_STATUSES['CANCELLED']
        ]);

        // Draft events by the organizer themselves and by others
        $draft_event_by_other_users = Event::factory()->create([
            'created_by' => $this->admin_user->id,
            'status' => EventConstants::EVENT_STATUSES['DRAFT']
        ]);

        $draft_event_by_self = Event::factory()->create([
            'created_by' => $this->organizer_user->id,
            'status' => EventConstants::EVENT_STATUSES['DRAFT']
        ]);

        // Organizers can view published or cancelled events, regardless of who created them
        $this->assertTrue($this->policy->view($this->organizer_user, $published_event_by_other_users));
        $this->assertTrue($this->policy->view($this->organizer_user, $cancelled_event_by_other_users));

        // Organizers can view their own draft events, but not drafts by others
        $this->assertTrue($this->policy->view($this->organizer_user, $draft_event_by_self));
        $this->assertFalse($this->policy->view($this->organizer_user, $draft_event_by_other_users));
    }

    public function test_attendee_can_view_published_cancelled_and_their_own_draft_events(): void
    {
        // Published and Cancelled Events by another user
        $published_event_by_other_users = Event::factory()->create([
            'created_by' => $this->admin_user->id,
            'status' => EventConstants::EVENT_STATUSES['PUBLISHED']
        ]);

        $cancelled_event_by_other_users = Event::factory()->create([
            'created_by' => $this->admin_user->id,
            'status' => EventConstants::EVENT_STATUSES['CANCELLED']
        ]);

        // Draft events by the attendee themselves and by others
        $draft_event_by_self = Event::factory()->create([
            'created_by' => $this->attendee_user->id,
            'status' => EventConstants::EVENT_STATUSES['DRAFT']
        ]);

        $draft_event_by_other_users = Event::factory()->create([
            'created_by' => $this->admin_user->id,
            'status' => EventConstants::EVENT_STATUSES['DRAFT']
        ]);

        // Attendees can view published or cancelled events, regardless of who created them
        $this->assertTrue($this->policy->view($this->attendee_user, $published_event_by_other_users));
        $this->assertTrue($this->policy->view($this->attendee_user, $cancelled_event_by_other_users));

        // Attendees can view their own draft events, but not drafts by others
        $this->assertTrue($this->policy->view($this->attendee_user, $draft_event_by_self));
        $this->assertFalse($this->policy->view($this->attendee_user, $draft_event_by_other_users));
    }

    public function test_event_create_policy()
    {
        // Admin should be able to create events
        $this->assertTrue($this->policy->create($this->admin_user));

        // Non-admin (Organizer and Attendee) cannot create events by default
        $this->assertFalse($this->policy->create($this->organizer_user));
        $this->assertFalse($this->policy->create($this->attendee_user));

        // Grant permission to non-admin users (Organizer and Attendee) to create events
        $this->organizer_user->givePermissionTo('create_event');
        $this->attendee_user->givePermissionTo('create_event');

        // Non-admin users (Organizer and Attendee) can create events if they have permission
        $this->assertTrue($this->policy->create($this->organizer_user));
        $this->assertTrue($this->policy->create($this->attendee_user));
    }

    public function test_event_update_policy()
    {
        foreach (EventConstants::EVENT_STATUSES as $status) {
            // Create event by other user (organizer)
            $event_by_organizer = Event::factory()->create([
                'created_by' => $this->organizer_user->id,
                'status' => $status
            ]);

            // Create event by admin (self)
            $event_by_admin = Event::factory()->create([
                'created_by' => $this->admin_user->id,
                'status' => $status
            ]);

            // Create event by attendee (self)
            $event_by_attendee = Event::factory()->create([
                'created_by' => $this->attendee_user->id,
                'status' => $status
            ]);

            // random user added as event organizer
            $appointed_event_manager = User::factory()->create();
            $event_by_organizer->organizers()->attach($appointed_event_manager->id);

            // Admin should be able to update any event
            $this->assertTrue($this->policy->update($this->admin_user, $event_by_organizer));
            $this->assertTrue($this->policy->update($this->admin_user, $event_by_admin));
            $this->assertTrue($this->policy->update($this->admin_user, $event_by_attendee));

            // Non-admin (organizer) should not be able to update other user's events
            $this->assertFalse($this->policy->update($this->organizer_user, $event_by_admin));
            $this->assertFalse($this->policy->update($this->organizer_user, $event_by_attendee));

            // Non-admin (attendee) should not be able to update other user's events
            $this->assertFalse($this->policy->update($this->attendee_user, $event_by_organizer));
            $this->assertFalse($this->policy->update($this->attendee_user, $event_by_admin));

            // Non-admin (attendee and organizer) should be able to update their own event
            $this->assertTrue($this->policy->update($this->organizer_user, $event_by_organizer));
            $this->assertTrue($this->policy->update($this->attendee_user, $event_by_attendee));

            // Non-admin (organizer appointed for an event) should not be able to update other user's events
            $this->assertFalse($this->policy->update($appointed_event_manager, $event_by_admin));
            $this->assertFalse($this->policy->update($appointed_event_manager, $event_by_attendee));

            // Non-admin (organizer appointed for an event) should be able to update events they manage
            $this->assertTrue($this->policy->update($appointed_event_manager, $event_by_organizer));
        }
    }

    public function test_event_delete_policy()
    {
        foreach (EventConstants::EVENT_STATUSES as $status) {
            // Create event by other user (organizer)
            $event_by_organizer = Event::factory()->create([
                'created_by' => $this->organizer_user->id,
                'status' => $status
            ]);

            // Create event by admin (self)
            $event_by_admin = Event::factory()->create([
                'created_by' => $this->admin_user->id,
                'status' => $status
            ]);

            // Create event by attendee (self)
            $event_by_attendee = Event::factory()->create([
                'created_by' => $this->attendee_user->id,
                'status' => $status
            ]);

            // Admin should be able to delete any event
            $this->assertTrue($this->policy->delete($this->admin_user, $event_by_organizer));
            $this->assertTrue($this->policy->delete($this->admin_user, $event_by_admin));
            $this->assertTrue($this->policy->delete($this->admin_user, $event_by_attendee));

            // Non-admin (organizer) should not be able to delete other user's events
            $this->assertFalse($this->policy->delete($this->organizer_user, $event_by_admin));
            $this->assertFalse($this->policy->delete($this->organizer_user, $event_by_attendee));

            // Non-admin (attendee) should not be able to delete other user's events
            $this->assertFalse($this->policy->delete($this->attendee_user, $event_by_organizer));
            $this->assertFalse($this->policy->delete($this->attendee_user, $event_by_admin));

            // Non-admin (attendee and organizer) should be able to delete their own event
            $this->assertTrue($this->policy->delete($this->organizer_user, $event_by_organizer));
            $this->assertTrue($this->policy->delete($this->attendee_user, $event_by_attendee));
        }
    }

    public function test_event_restore_policy(): void
    {
        foreach (EventConstants::EVENT_STATUSES as $status) {
            $event_by_admin = Event::factory()->create([
                'created_by' => $this->admin_user->id,
                'status' => $status
            ]);

            $event_by_organizer = Event::factory()->create([
                'created_by' => $this->organizer_user->id,
                'status' => $status
            ]);

            // Admin can restore any event
            $this->assertTrue($this->policy->restore($this->admin_user, $event_by_admin));
            $this->assertTrue($this->policy->restore($this->admin_user, $event_by_organizer));

            // Organizer can restore their own event
            $this->assertTrue($this->policy->restore($this->organizer_user, $event_by_organizer));

            // Other non-creator, non-admin cannot restore events
            $this->assertFalse($this->policy->restore($this->organizer_user, $event_by_admin));
            $this->assertFalse($this->policy->restore($this->attendee_user, $event_by_admin));
        }
    }

    public function test_event_force_delete_policy(): void
    {
        foreach (EventConstants::EVENT_STATUSES as $status) {
            $event_by_admin = Event::factory()->create([
                'created_by' => $this->admin_user->id,
                'status' => $status
            ]);

            $event_by_organizer = Event::factory()->create([
                'created_by' => $this->organizer_user->id,
                'status' => $status
            ]);

            // Admin can force delete any event
            $this->assertTrue($this->policy->forceDelete($this->admin_user, $event_by_admin));
            $this->assertTrue($this->policy->forceDelete($this->admin_user, $event_by_organizer));

            // Non-admin users (even the creator) cannot force delete events
            $this->assertFalse($this->policy->forceDelete($this->organizer_user, $event_by_admin));
            $this->assertFalse($this->policy->forceDelete($this->organizer_user, $event_by_organizer));
            $this->assertFalse($this->policy->forceDelete($this->attendee_user, $event_by_admin));
            $this->assertFalse($this->policy->forceDelete($this->attendee_user, $event_by_organizer));
        }
    }
}
