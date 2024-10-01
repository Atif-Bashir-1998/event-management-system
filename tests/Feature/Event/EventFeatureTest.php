<?php

namespace Tests\Feature\Event;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use App\Constants\RolePermission\Constants as RolePermissionConstants;
use App\Constants\Event\Constants as EventConstants;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class EventFeatureTest extends TestCase
{
    use RefreshDatabase;

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

        $organizer_role = Role::create(['name' => RolePermissionConstants::DEFAULT_ROLES['ORGANIZER']]);
        $organizer_role->syncPermissions(['view_event', 'create_event', 'update_event']);

        $attendee_role = Role::create(['name' => RolePermissionConstants::DEFAULT_ROLES['ATTENDEE']]);
        $attendee_role->syncPermissions(['view_event']);

        // create users with roles
        $this->admin_user = User::factory()->create()->assignRole($admin_role);
        $this->organizer_user = User::factory()->create()->assignRole($organizer_role);
        $this->attendee_user = User::factory()->create()->assignRole($attendee_role);
    }

    // viewing tests
    public function test_events_page_renders(): void
    {
        $admin_response = $this->actingAs($this->admin_user)->get(route('event.index'));
        $admin_response->assertStatus(200);
        $admin_response->assertInertia(function ($page) {
            $page->component('Event/Index')
                ->has('events');
        });

        /** @var User $random_user */
        $random_user = User::factory()->create();
        $random_user->givePermissionTo('view_event');
        $random_user_response = $this->actingAs($random_user)->get(route('event.index'));
        $random_user_response->assertStatus(200);
        $random_user_response->assertInertia(function ($page) {
            $page->component('Event/Index')
                ->has('events');
        });
    }

    public function test_admin_views_all_events_on_events_page(): void
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

            // response for admin
            $admin_response = $this->actingAs($this->admin_user)->get(route('event.index'));
            $admin_response->assertStatus(200);
            $admin_response->assertInertia(function ($page) {
                $page->component('Event/Index')
                    ->has('events', 3);
            });

            Event::truncate();
        }
    }

    public function test_non_admins_only_see_published_or_cancelled_events(): void
    {
        $draft_event_by_admin = Event::factory()->create([
            'created_by' => $this->admin_user->id,
            'status' => EventConstants::EVENT_STATUSES['DRAFT']
        ]);

        $published_event_by_organizer = Event::factory()->create([
            'created_by' => $this->organizer_user->id,
            'status' => EventConstants::EVENT_STATUSES['PUBLISHED']
        ]);
        $cancelled_event_by_organizer = Event::factory()->create([
            'created_by' => $this->organizer_user->id,
            'status' => EventConstants::EVENT_STATUSES['CANCELLED']
        ]);

        $published_event_by_attendee = Event::factory()->create([
            'created_by' => $this->attendee_user->id,
            'status' => EventConstants::EVENT_STATUSES['PUBLISHED']
        ]);
        $cancelled_event_by_attendee = Event::factory()->create([
            'created_by' => $this->attendee_user->id,
            'status' => EventConstants::EVENT_STATUSES['CANCELLED']
        ]);

        // random user added as event organizer
        /** @var User $appointed_event_manager */
        $appointed_event_manager = User::factory()->create()->givePermissionTo('view_event');
        $published_event_by_organizer->organizers()->attach($appointed_event_manager->id);

        $organizer_response = $this->actingAs($this->organizer_user)->get(route('event.index'));
        $organizer_response->assertStatus(200);
        $organizer_response->assertInertia(function ($page) {
            $page->component('Event/Index')
                ->has('events', 4);

            $events = $page->toArray()['props']['events'];

            // Check that no event has a status of 'draft'
            $this->assertTrue(collect($events)->every(function ($event) {
                return $event['status'] !== 'draft';
            }));
        });

        $attendee_response = $this->actingAs($this->attendee_user)->get(route('event.index'));
        $attendee_response->assertStatus(200);
        $attendee_response->assertInertia(function ($page) {
            $page->component('Event/Index')
                ->has('events', 4);
        });

        $appointed_event_manager_response = $this->actingAs($appointed_event_manager)->get(route('event.index'));
        $appointed_event_manager_response->assertStatus(200);
        $appointed_event_manager_response->assertInertia(function ($page) {
            $page->component('Event/Index')
                ->has('events', 4);

            $events = $page->toArray()['props']['events'];

            // Check that no event has a status of 'draft'
            $this->assertTrue(collect($events)->every(function ($event) {
                return $event['status'] !== 'draft';
            }));
        });
    }

    public function test_non_admins_only_see_draft_events_created_by_them_or_assigned_to_them(): void
    {
        $draft_event_by_admin = Event::factory()->create([
            'created_by' => $this->admin_user->id,
            'status' => EventConstants::EVENT_STATUSES['DRAFT']
        ]);

        $draft_event_by_organizer = Event::factory()->create([
            'created_by' => $this->organizer_user->id,
            'status' => EventConstants::EVENT_STATUSES['DRAFT']
        ]);

        $draft_event_by_attendee = Event::factory()->create([
            'created_by' => $this->attendee_user->id,
            'status' => EventConstants::EVENT_STATUSES['DRAFT']
        ]);

        // random user added as event organizer
        /** @var User $appointed_event_manager */
        $appointed_event_manager = User::factory()->create()->givePermissionTo('view_event');
        $draft_event_by_organizer->organizers()->attach($appointed_event_manager->id);

        $organizer_response = $this->actingAs($this->organizer_user)->get(route('event.index'));
        $organizer_response->assertStatus(200);
        $organizer_response->assertInertia(function ($page) {
            $page->component('Event/Index')
                ->has('events', 1);

            $events = $page->toArray()['props']['events'];

            // Check that one event has a status of 'draft'
            $this->assertTrue(collect($events)->every(function ($event) {
                return $event['status'] === 'draft' && $event['created_by'] === $this->organizer_user->id;
            }));
        });

        $attendee_response = $this->actingAs($this->attendee_user)->get(route('event.index'));
        $attendee_response->assertStatus(200);
        $attendee_response->assertInertia(function ($page) {
            $page->component('Event/Index')
                ->has('events', 1);

            $events = $page->toArray()['props']['events'];

            // Check that one event has a status of 'draft'
            $this->assertTrue(collect($events)->every(function ($event) {
                return $event['status'] === 'draft' && $event['created_by'] === $this->attendee_user->id;
            }));
        });

        $appointed_event_manager_response = $this->actingAs($appointed_event_manager)->get(route('event.index'));
        $appointed_event_manager_response->assertStatus(200);
        $appointed_event_manager_response->assertInertia(function ($page) {
            $page->component('Event/Index')
                ->has('events', 1);

            $events = $page->toArray()['props']['events'];

            // Check that one event has a status of 'draft' where user is an event manager
            $this->assertTrue(collect($events)->every(function ($event) {
                return $event['status'] === 'draft' && $event['created_by'] === $this->organizer_user->id;
                ;
            }));
        });
    }

    // CREATE
    public function test_create_event_page_renders_for_authorized_users(): void
    {
        $admin_response = $this->actingAs($this->admin_user)->get(route('event.create'));
        $admin_response->assertStatus(200);
        $admin_response->assertInertia(function ($page) {
            $page->component('Event/Create');
        });

        /** @var User $random_user */
        $random_user = User::factory()->create()->givePermissionTo('create_event');
        $random_user_response = $this->actingAs($random_user)->get(route('event.create'));
        $random_user_response->assertStatus(200);
        $random_user_response->assertInertia(function ($page) {
            $page->component('Event/Create');
        });
    }

    public function test_create_event_page_does_not_render_for_unauthorized_users(): void
    {
        /** @var User $user_without_permission */
        $user_without_permission = User::factory()->create();

        $random_user_without_permission_response = $this->actingAs(user: $user_without_permission)->get(route('event.create'));
        $random_user_without_permission_response->assertStatus(403);
    }

    public function test_new_event_can_be_created_by_authorized_users(): void
    {
        $this->withoutExceptionHandling();
        $dummy_event_data = Event::factory()->make()->toArray();

        $admin_response = $this->actingAs($this->admin_user)->post(route('event.store', $dummy_event_data));
        $admin_response->assertSessionHasNoErrors();
        $admin_response->assertSessionHas('success');

        $this->assertCount(1, Event::all());

        /** @var User $random_user */
        $random_user = User::factory()->create()->givePermissionTo('create_event');
        $random_user_response = $this->actingAs($random_user)->post(route('event.store', $dummy_event_data));
        $random_user_response->assertSessionHasNoErrors();
        $random_user_response->assertSessionHas('success');

        $this->assertCount(2, Event::all());
    }

    public function test_new_event_cannot_be_created_by_unauthorized_users(): void
    {
        /** @var User $user_without_permission */
        $user_without_permission = User::factory()->create();
        $dummy_event_data = Event::factory()->make()->toArray();

        $response = $this->actingAs($user_without_permission)->post(route('event.store', $dummy_event_data));
        $response->assertStatus(403);

        $this->assertCount(0, Event::all());
    }

    // EDIT/UPDATE
    public function test_edit_event_page_renders_for_authorized_users(): void
    {
        $this->withoutExceptionHandling();
        $random_user = User::factory()->create()->givePermissionTo('update_event');

        $user_created_event = Event::factory()->create([
            'created_by' => $random_user->id
        ]);

        $admin_response = $this->actingAs($this->admin_user)->get(route('event.edit', ['event' => $user_created_event]));
        $admin_response->assertStatus(200);
        $admin_response->assertInertia(function ($page) {
            $page->component('Event/Edit');
        });

        /** @var User $random_user */
        $random_user_response = $this->actingAs($random_user)->get(route('event.edit', ['event' => $user_created_event]));
        $random_user_response->assertStatus(200);
        $random_user_response->assertInertia(function ($page) {
            $page->component('Event/Edit');
        });
    }

    public function test_edit_event_page_does_not_render_for_unauthorized_users(): void
    {
        $random_user_with_permission = User::factory()->create()->givePermissionTo('update_event');

        /** @var User $random_user_without_permission */
        $random_user_without_permission = User::factory()->create();

        $user_created_event = Event::factory()->create([
            'created_by' => $random_user_with_permission->id
        ]);
        $admin_created_event = Event::factory()->create([
            'created_by' => $this->admin_user->id
        ]);

        // user with permission trying to access event they are not authorized to access
        $random_user_with_permission_response = $this->actingAs($random_user_with_permission)->get(route('event.edit', ['event' => $admin_created_event]));
        $random_user_with_permission_response->assertStatus(403);

        // user without permission trying to access edit page of
        $random_user_without_permission_response = $this->actingAs(user: $random_user_without_permission)->get(route('event.edit', ['event' => $user_created_event]));
        $random_user_without_permission_response->assertStatus(403);
    }

    public function test_existing_event_can_be_updated_by_authorized_users(): void
    {
        $this->withoutExceptionHandling();

        $random_user = User::factory()->create()->givePermissionTo('update_event');
        $updated_event_data_by_admin = Event::factory()->make()->toArray();
        $updated_event_data_by_user = Event::factory()->make()->toArray();

        $user_created_event = Event::factory()->create([
            'created_by' => $random_user->id
        ]);

        $admin_response = $this->actingAs($this->admin_user)->patch(route('event.update', ['event' => $user_created_event]), $updated_event_data_by_admin);
        $admin_response->assertStatus(200);
        $admin_response->assertSessionHas('success');
        $this->assertEquals($updated_event_data_by_admin['name'], $user_created_event->fresh->name);

        // /** @var User $random_user */
        // $random_user_response = $this->actingAs($random_user)->patch(route('event.update', ['event' => $user_created_event]), $updated_event_data_by_user);
        // $random_user_response->assertStatus(200);
        // $random_user_response->assertInertia(function ($page) {
        //     $page->component('Event/Edit');
        // });
    }

    // public function test_existing_event_can_be_updated(): void
    // {
    //     $this->withoutExceptionHandling();
    //     $existing_event = Event::factory()->create([
    //         'created_by' => $this->admin_user->id
    //     ]);
    //     $updated_dummy_event_data = Event::factory()->make()->toArray();

    //     $admin_response = $this->actingAs($this->admin_user)->patch(route('event.update', ['event' => $existing_event], $updated_dummy_event_data));
    //     $admin_response->assertSessionHasNoErrors();
    //     $admin_response->assertSessionHas('success');

    //     $this->assertCount(1, Event::all());
    //     $this->assertEquals()

    //     /** @var User $random_user */
    //     $random_user = User::factory()->create()->givePermissionTo('create_event');
    //     $random_user_response = $this->actingAs($random_user)->post(route('event.store', $dummy_event_data));
    //     $random_user_response->assertSessionHasNoErrors();
    //     $random_user_response->assertSessionHas('success');

    //     $this->assertCount(2, Event::all());
    // }
}
