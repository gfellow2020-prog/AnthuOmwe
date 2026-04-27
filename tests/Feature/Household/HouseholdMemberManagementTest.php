<?php

namespace Tests\Feature\Household;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HouseholdMemberManagementTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser(): User
    {
        $user = User::factory()->create(['name' => 'Household Admin']);
        $this->actingAs($user);

        return $user;
    }

    public function test_member_can_be_added_to_household_and_set_as_head(): void
    {
        $this->actingAsUser();

        DB::table('households')->insert([
            'household_id' => 'HH-TEST-001',
            'head_of_house' => 'Old Head',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post(route('households.members.store', ['ref' => 'HH-TEST-001']), [
            'full_name' => 'New Household Head',
            'gender' => 'Female',
            'date_of_birth' => '1994-04-15',
            'phone_number' => '+260970000001',
            'relationship_to_head' => 'Head',
        ]);

        $response->assertRedirect(route('households.show', ['ref' => 'HH-TEST-001']));

        $this->assertDatabaseHas('patients', [
            'full_name' => 'New Household Head',
            'household_id' => 'HH-TEST-001',
            'relationship_to_head' => 'Head',
            'household_head_of_house' => 'New Household Head',
        ]);

        $this->assertDatabaseHas('households', [
            'household_id' => 'HH-TEST-001',
            'head_of_house' => 'New Household Head',
        ]);
    }

    public function test_removing_head_reassigns_household_head_to_remaining_member(): void
    {
        $this->actingAsUser();

        DB::table('households')->insert([
            'household_id' => 'HH-TEST-010',
            'head_of_house' => 'Current Head',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('patients')->insert([
            [
                'patient_id' => 'PTESTHEAD01',
                'full_name' => 'Current Head',
                'gender' => 'Male',
                'household_id' => 'HH-TEST-010',
                'relationship_to_head' => 'Head',
                'household_head_of_house' => 'Current Head',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'patient_id' => 'PTESTMEM01',
                'full_name' => 'Remaining Member',
                'gender' => 'Female',
                'household_id' => 'HH-TEST-010',
                'relationship_to_head' => 'Member',
                'household_head_of_house' => 'Current Head',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->delete(route('households.members.remove', [
            'ref' => 'HH-TEST-010',
            'patientRef' => 'PTESTHEAD01',
        ]));

        $response->assertRedirect(route('households.show', ['ref' => 'HH-TEST-010']));

        $this->assertDatabaseHas('patients', [
            'patient_id' => 'PTESTHEAD01',
            'household_id' => null,
            'relationship_to_head' => null,
        ]);

        $this->assertDatabaseHas('patients', [
            'patient_id' => 'PTESTMEM01',
            'household_id' => 'HH-TEST-010',
            'relationship_to_head' => 'Head',
            'household_head_of_house' => 'Remaining Member',
        ]);

        $this->assertDatabaseHas('households', [
            'household_id' => 'HH-TEST-010',
            'head_of_house' => 'Remaining Member',
        ]);
    }

    public function test_member_can_be_transferred_to_another_household_as_head(): void
    {
        $this->actingAsUser();

        DB::table('households')->insert([
            [
                'household_id' => 'HH-TEST-SRC',
                'head_of_house' => 'Source Head',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'household_id' => 'HH-TEST-TGT',
                'head_of_house' => 'Target Head',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('patients')->insert([
            [
                'patient_id' => 'PSOURCE01',
                'full_name' => 'Source Head',
                'gender' => 'Male',
                'household_id' => 'HH-TEST-SRC',
                'relationship_to_head' => 'Head',
                'household_head_of_house' => 'Source Head',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'patient_id' => 'PTRANS001',
                'full_name' => 'Transfer Candidate',
                'gender' => 'Female',
                'household_id' => 'HH-TEST-SRC',
                'relationship_to_head' => 'Member',
                'household_head_of_house' => 'Source Head',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'patient_id' => 'PTARGET01',
                'full_name' => 'Target Head',
                'gender' => 'Male',
                'household_id' => 'HH-TEST-TGT',
                'relationship_to_head' => 'Head',
                'household_head_of_house' => 'Target Head',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->post(route('households.members.transfer', [
            'ref' => 'HH-TEST-SRC',
            'patientRef' => 'PTRANS001',
        ]), [
            'target_household_id' => 'HH-TEST-TGT',
            'transfer_as_head' => 1,
        ]);

        $response->assertRedirect(route('households.show', ['ref' => 'HH-TEST-SRC']));

        $this->assertDatabaseHas('patients', [
            'patient_id' => 'PTRANS001',
            'household_id' => 'HH-TEST-TGT',
            'relationship_to_head' => 'Head',
            'household_head_of_house' => 'Transfer Candidate',
        ]);

        $this->assertDatabaseHas('patients', [
            'patient_id' => 'PTARGET01',
            'household_id' => 'HH-TEST-TGT',
            'relationship_to_head' => 'Member',
            'household_head_of_house' => 'Transfer Candidate',
        ]);

        $this->assertDatabaseHas('households', [
            'household_id' => 'HH-TEST-TGT',
            'head_of_house' => 'Transfer Candidate',
        ]);
    }
}
