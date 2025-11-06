<?php

namespace Tests\Unit\Policies;

use App\Models\Custodianship;
use App\Models\User;
use App\Policies\CustodianshipPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustodianshipPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected CustodianshipPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new CustodianshipPolicy;
    }

    public function test_view_any_returns_true_for_any_user(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_view_returns_true_for_owner(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->policy->view($user, $custodianship));
    }

    public function test_view_returns_false_for_non_owner(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $custodianship = Custodianship::factory()->create(['user_id' => $owner->id]);

        $this->assertFalse($this->policy->view($otherUser, $custodianship));
    }

    public function test_create_returns_true_for_any_user(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($this->policy->create($user));
    }

    public function test_update_returns_true_for_owner(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->policy->update($user, $custodianship));
    }

    public function test_update_returns_false_for_non_owner(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $custodianship = Custodianship::factory()->create(['user_id' => $owner->id]);

        $this->assertFalse($this->policy->update($otherUser, $custodianship));
    }

    public function test_delete_returns_true_for_owner(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->policy->delete($user, $custodianship));
    }

    public function test_delete_returns_false_for_non_owner(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $custodianship = Custodianship::factory()->create(['user_id' => $owner->id]);

        $this->assertFalse($this->policy->delete($otherUser, $custodianship));
    }

    public function test_activate_returns_true_for_owner(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->policy->activate($user, $custodianship));
    }

    public function test_activate_returns_false_for_non_owner(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $custodianship = Custodianship::factory()->create(['user_id' => $owner->id]);

        $this->assertFalse($this->policy->activate($otherUser, $custodianship));
    }

    public function test_restore_returns_true_for_owner(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->policy->restore($user, $custodianship));
    }

    public function test_restore_returns_false_for_non_owner(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $custodianship = Custodianship::factory()->create(['user_id' => $owner->id]);

        $this->assertFalse($this->policy->restore($otherUser, $custodianship));
    }

    public function test_force_delete_returns_true_for_owner(): void
    {
        $user = User::factory()->create();
        $custodianship = Custodianship::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->policy->forceDelete($user, $custodianship));
    }

    public function test_force_delete_returns_false_for_non_owner(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $custodianship = Custodianship::factory()->create(['user_id' => $owner->id]);

        $this->assertFalse($this->policy->forceDelete($otherUser, $custodianship));
    }
}
