<?php

namespace Tests\Feature;

use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminFeaturedLoanTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_edit_and_delete_featured_loans(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.featured-loans.index'))
            ->assertOk()
            ->assertSee('Add featured loan');

        $this->actingAs($admin)
            ->post(route('admin.featured-loans.store'), [
                'title' => 'Festival Cash',
                'amount' => 22000,
                'minimum_amount' => 2000,
                'maximum_amount' => 30000,
                'description' => 'Seasonal featured offer.',
            ])
            ->assertRedirect(route('admin.featured-loans.index'));

        $loan = Loan::query()->where('title', 'Festival Cash')->first();

        $this->assertTrue($loan->is_featured);
        $this->assertNull($loan->user_id);
        $this->assertSame(LoanStatus::Approved, $loan->status);

        $this->actingAs($admin)
            ->put(route('admin.featured-loans.update', $loan), [
                'title' => 'Festival Cash Plus',
                'amount' => 25000,
                'minimum_amount' => 2000,
                'maximum_amount' => 30000,
                'description' => 'Updated featured offer.',
            ])
            ->assertRedirect(route('admin.featured-loans.index'));

        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'title' => 'Festival Cash Plus',
            'amount' => 25000,
            'is_featured' => true,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.featured-loans.destroy', $loan))
            ->assertRedirect(route('admin.featured-loans.index'));

        $this->assertDatabaseMissing('loans', ['id' => $loan->id]);
    }

    public function test_featured_loan_created_by_admin_appears_on_customer_home(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();

        $this->actingAs($admin)
            ->post(route('admin.featured-loans.store'), [
                'title' => 'Home Offer',
                'amount' => 12000,
            ]);

        $this->actingAs($customer)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Home Offer')
            ->assertSee('Apply');
    }

    public function test_admin_can_add_a_featured_image(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();

        $this->actingAs($admin)
            ->post(route('admin.featured-loans.store'), [
                'title' => 'Image Offer',
                'amount' => 9000,
                'featured_image' => $this->fakeScreenshot('offer.png'),
            ])
            ->assertRedirect(route('admin.featured-loans.index'));

        $loan = Loan::query()->where('title', 'Image Offer')->first();

        $this->assertNotNull($loan->featured_image);
        Storage::disk('public')->assertExists($loan->featured_image);

        $this->actingAs($customer)
            ->get(route('featured-loans.image', $loan))
            ->assertOk();
    }
}
