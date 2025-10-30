<?php

namespace Tests\Feature;

use App\Models\Custodianship;
use App\Models\Recipient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustodianshipControllerIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_users_cannot_access_custodianships_index(): void
    {
        $response = $this->get(route('custodianships.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_custodianships_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('custodianships.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Custodianships/Index'));
    }

    public function test_user_can_only_see_their_own_custodianships(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $userCustodianship = Custodianship::factory()->create(['user_id' => $user->id, 'name' => 'My Custodianship']);
        $otherCustodianship = Custodianship::factory()->create(['user_id' => $otherUser->id, 'name' => 'Other Custodianship']);

        $response = $this->actingAs($user)->get(route('custodianships.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Custodianships/Index')
            ->has('custodianships', 1)
            ->where('custodianships.0.name', 'My Custodianship')
        );
    }

    public function test_index_returns_empty_collection_when_user_has_no_custodianships(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('custodianships.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Custodianships/Index')
            ->has('custodianships', 0)
        );
    }

    public function test_index_returns_user_custodianships_with_recipients_count(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->create(['user_id' => $user->id]);
        Recipient::factory()->count(2)->create(['custodianship_id' => $custodianship->id]);

        $response = $this->actingAs($user)->get(route('custodianships.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Custodianships/Index')
            ->has('custodianships', 1)
            ->where('custodianships.0.recipientsCount', 2)
        );
    }

    public function test_index_includes_correct_user_resource_data(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response = $this->actingAs($user)->get(route('custodianships.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Custodianships/Index')
            ->has('user')
            ->where('user.name', 'John Doe')
            ->where('user.email', 'john@example.com')
            ->has('user.id')
            ->has('user.emailVerified')
            ->has('user.createdAt')
        );
    }

    public function test_index_includes_correct_custodianship_collection_resource_data(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Custodianship',
            'status' => 'active',
            'interval' => 'P30D',
        ]);

        $response = $this->actingAs($user)->get(route('custodianships.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Custodianships/Index')
            ->has('custodianships', 1)
            ->where('custodianships.0.name', 'Test Custodianship')
            ->where('custodianships.0.status', 'active')
            ->where('custodianships.0.interval', 'P30D')
            ->has('custodianships.0.id')
            ->has('custodianships.0.uuid')
            ->has('custodianships.0.recipientsCount')
            ->has('custodianships.0.lastResetAt')
            ->has('custodianships.0.nextTriggerAt')
            ->has('custodianships.0.createdAt')
            ->has('custodianships.0.updatedAt')
        );
    }

    public function test_custodianships_are_ordered_by_default_scope(): void
    {
        $user = User::factory()->create();

        $draft = Custodianship::factory()->create([
            'user_id' => $user->id,
            'name' => 'Draft',
            'status' => 'draft',
        ]);

        $active = Custodianship::factory()->create([
            'user_id' => $user->id,
            'name' => 'Active',
            'status' => 'active',
            'next_trigger_at' => now()->addDays(10),
        ]);

        $completed = Custodianship::factory()->create([
            'user_id' => $user->id,
            'name' => 'Completed',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($user)->get(route('custodianships.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Custodianships/Index')
            ->has('custodianships', 3)
            ->where('custodianships.0.name', 'Active')
            ->where('custodianships.1.name', 'Draft')
            ->where('custodianships.2.name', 'Completed')
        );
    }

    public function test_active_custodianships_appear_before_drafts(): void
    {
        $user = User::factory()->create();

        Custodianship::factory()->create([
            'user_id' => $user->id,
            'name' => 'Draft First',
            'status' => 'draft',
            'created_at' => now()->subHours(1),
        ]);

        Custodianship::factory()->create([
            'user_id' => $user->id,
            'name' => 'Active Second',
            'status' => 'active',
            'next_trigger_at' => now()->addDays(30),
            'created_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('custodianships.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Custodianships/Index')
            ->has('custodianships', 2)
            ->where('custodianships.0.name', 'Active Second')
            ->where('custodianships.1.name', 'Draft First')
        );
    }

    public function test_active_custodianships_sorted_by_next_trigger_at_ascending(): void
    {
        $user = User::factory()->create();

        Custodianship::factory()->create([
            'user_id' => $user->id,
            'name' => 'Expires in 30 days',
            'status' => 'active',
            'next_trigger_at' => now()->addDays(30),
        ]);

        Custodianship::factory()->create([
            'user_id' => $user->id,
            'name' => 'Expires in 7 days',
            'status' => 'active',
            'next_trigger_at' => now()->addDays(7),
        ]);

        Custodianship::factory()->create([
            'user_id' => $user->id,
            'name' => 'Expires in 1 day',
            'status' => 'active',
            'next_trigger_at' => now()->addDay(),
        ]);

        $response = $this->actingAs($user)->get(route('custodianships.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Custodianships/Index')
            ->has('custodianships', 3)
            ->where('custodianships.0.name', 'Expires in 1 day')
            ->where('custodianships.1.name', 'Expires in 7 days')
            ->where('custodianships.2.name', 'Expires in 30 days')
        );
    }

    public function test_draft_custodianships_sorted_by_created_at_descending(): void
    {
        $user = User::factory()->create();

        Custodianship::factory()->create([
            'user_id' => $user->id,
            'name' => 'Old Draft',
            'status' => 'draft',
            'created_at' => now()->subDays(10),
        ]);

        Custodianship::factory()->create([
            'user_id' => $user->id,
            'name' => 'New Draft',
            'status' => 'draft',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('custodianships.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Custodianships/Index')
            ->has('custodianships', 2)
            ->where('custodianships.0.name', 'New Draft')
            ->where('custodianships.1.name', 'Old Draft')
        );
    }

    public function test_completed_custodianships_sorted_by_created_at_descending(): void
    {
        $user = User::factory()->create();

        Custodianship::factory()->create([
            'user_id' => $user->id,
            'name' => 'Old Completed',
            'status' => 'completed',
            'created_at' => now()->subDays(10),
        ]);

        Custodianship::factory()->create([
            'user_id' => $user->id,
            'name' => 'New Completed',
            'status' => 'completed',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('custodianships.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Custodianships/Index')
            ->has('custodianships', 2)
            ->where('custodianships.0.name', 'New Completed')
            ->where('custodianships.1.name', 'Old Completed')
        );
    }

    public function test_recipients_count_is_zero_when_no_recipients(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('custodianships.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Custodianships/Index')
            ->has('custodianships', 1)
            ->where('custodianships.0.recipientsCount', 0)
        );
    }

    public function test_recipients_count_reflects_actual_recipients(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->create(['user_id' => $user->id]);
        Recipient::factory()->count(2)->create(['custodianship_id' => $custodianship->id]);

        $response = $this->actingAs($user)->get(route('custodianships.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Custodianships/Index')
            ->has('custodianships', 1)
            ->where('custodianships.0.recipientsCount', 2)
        );
    }

    public function test_recipients_count_with_multiple_custodianships(): void
    {
        $user = User::factory()->create();

        $custodianship1 = Custodianship::factory()->create(['user_id' => $user->id, 'name' => 'First']);
        Recipient::factory()->count(1)->create(['custodianship_id' => $custodianship1->id]);

        $custodianship2 = Custodianship::factory()->create(['user_id' => $user->id, 'name' => 'Second']);
        Recipient::factory()->count(2)->create(['custodianship_id' => $custodianship2->id]);

        $response = $this->actingAs($user)->get(route('custodianships.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Custodianships/Index')
            ->has('custodianships', 2)
            ->where('custodianships.0.recipientsCount', 1)
            ->where('custodianships.1.recipientsCount', 2)
        );
    }

    public function test_index_displays_draft_custodianships(): void
    {
        $user = User::factory()->create();
        Custodianship::factory()->create(['user_id' => $user->id, 'status' => 'draft']);

        $response = $this->actingAs($user)->get(route('custodianships.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Custodianships/Index')
            ->has('custodianships', 1)
            ->where('custodianships.0.status', 'draft')
        );
    }

    public function test_index_displays_active_custodianships(): void
    {
        $user = User::factory()->create();
        Custodianship::factory()->create(['user_id' => $user->id, 'status' => 'active']);

        $response = $this->actingAs($user)->get(route('custodianships.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Custodianships/Index')
            ->has('custodianships', 1)
            ->where('custodianships.0.status', 'active')
        );
    }

    public function test_index_displays_completed_custodianships(): void
    {
        $user = User::factory()->create();
        Custodianship::factory()->create(['user_id' => $user->id, 'status' => 'completed']);

        $response = $this->actingAs($user)->get(route('custodianships.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Custodianships/Index')
            ->has('custodianships', 1)
            ->where('custodianships.0.status', 'completed')
        );
    }

    public function test_index_displays_delivery_failed_custodianships(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->create([
            'user_id' => $user->id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($user)->get(route('custodianships.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Custodianships/Index')
            ->has('custodianships', 1)
            ->where('custodianships.0.status', 'completed')
        );
    }

    public function test_index_displays_mixed_status_custodianships(): void
    {
        $user = User::factory()->create();

        Custodianship::factory()->create(['user_id' => $user->id, 'status' => 'draft']);
        Custodianship::factory()->create(['user_id' => $user->id, 'status' => 'active']);
        Custodianship::factory()->create(['user_id' => $user->id, 'status' => 'completed']);
        Custodianship::factory()->create([
            'user_id' => $user->id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($user)->get(route('custodianships.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Custodianships/Index')
            ->has('custodianships', 4)
        );
    }

    public function test_index_renders_correct_inertia_component(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('custodianships.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Custodianships/Index'));
    }

    public function test_index_response_contains_user_prop(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('custodianships.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Custodianships/Index')
            ->has('user')
        );
    }

    public function test_index_response_contains_custodianships_prop(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('custodianships.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Custodianships/Index')
            ->has('custodianships')
        );
    }

    public function test_inertia_props_are_resolved(): void
    {
        $user = User::factory()->create();
        Custodianship::factory()->create(['user_id' => $user->id, 'name' => 'Test']);

        $response = $this->actingAs($user)->get(route('custodianships.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Custodianships/Index')
            ->has('user.id')
            ->has('user.name')
            ->has('custodianships.0.id')
            ->has('custodianships.0.name')
        );
    }

    public function test_index_handles_custodianships_with_null_dates(): void
    {
        $user = User::factory()->create();
        Custodianship::factory()->create([
            'user_id' => $user->id,
            'last_reset_at' => null,
            'next_trigger_at' => null,
        ]);

        $response = $this->actingAs($user)->get(route('custodianships.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Custodianships/Index')
            ->has('custodianships', 1)
        );
    }

    public function test_index_handles_large_number_of_custodianships(): void
    {
        $user = User::factory()->create();
        Custodianship::factory()->count(50)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('custodianships.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Custodianships/Index')
            ->has('custodianships', 50)
        );
    }

    public function test_index_with_expired_custodianships(): void
    {
        $user = User::factory()->create();
        Custodianship::factory()->create([
            'user_id' => $user->id,
            'name' => 'Expired',
            'status' => 'active',
            'next_trigger_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)->get(route('custodianships.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Custodianships/Index')
            ->has('custodianships', 1)
            ->where('custodianships.0.name', 'Expired')
        );
    }

    public function test_index_with_custodianships_near_expiration(): void
    {
        $user = User::factory()->create();
        Custodianship::factory()->create([
            'user_id' => $user->id,
            'name' => 'Near Expiration',
            'status' => 'active',
            'next_trigger_at' => now()->addDays(3),
        ]);

        $response = $this->actingAs($user)->get(route('custodianships.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Custodianships/Index')
            ->has('custodianships', 1)
            ->where('custodianships.0.name', 'Near Expiration')
        );
    }

    public function test_index_displays_all_custodianships_up_to_free_limit(): void
    {
        $user = User::factory()->create();
        Custodianship::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('custodianships.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Custodianships/Index')
            ->has('custodianships', 3)
        );
    }

    public function test_index_displays_custodianships_beyond_free_limit(): void
    {
        $user = User::factory()->create();
        Custodianship::factory()->count(5)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('custodianships.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Custodianships/Index')
            ->has('custodianships', 5)
        );
    }
}
