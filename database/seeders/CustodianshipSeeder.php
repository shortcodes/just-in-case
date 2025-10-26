<?php

namespace Database\Seeders;

use App\Models\Custodianship;
use App\Models\CustodianshipMessage;
use App\Models\Recipient;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustodianshipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::find(1);

        if (! $user) {
            $this->command->info('User with ID 1 not found. Skipping custodianship seeding.');

            return;
        }

        $this->command->info('Seeding custodianships for user: '.$user->email);

        // 1. Expired Success - Completed and delivered
        $custodianship1 = Custodianship::create([
            'user_id' => $user->id,
            'name' => 'Family Emergency Info (Completed)',
            'status' => 'completed',
            'delivery_status' => 'delivered',
            'interval' => 'P90D',
            'activated_at' => now()->subDays(120),
            'last_reset_at' => now()->subDays(95),
            'next_trigger_at' => now()->subDays(5),
        ]);

        CustodianshipMessage::create([
            'custodianship_id' => $custodianship1->id,
            'content' => 'This message contains important family emergency contacts and medical information. It was successfully delivered to recipients.',
        ]);

        Recipient::create([
            'custodianship_id' => $custodianship1->id,
            'email' => 'family.member1@example.com',
        ]);

        Recipient::create([
            'custodianship_id' => $custodianship1->id,
            'email' => 'family.member2@example.com',
        ]);

        // 2. Expired Failed - Completed but delivery failed
        $custodianship2 = Custodianship::create([
            'user_id' => $user->id,
            'name' => 'Bank Account Details (Failed)',
            'status' => 'completed',
            'delivery_status' => 'failed',
            'interval' => 'P30D',
            'activated_at' => now()->subDays(40),
            'last_reset_at' => now()->subDays(32),
            'next_trigger_at' => now()->subDays(2),
        ]);

        CustodianshipMessage::create([
            'custodianship_id' => $custodianship2->id,
            'content' => 'Important banking information and account access details. Delivery failed due to invalid recipient email.',
        ]);

        Recipient::create([
            'custodianship_id' => $custodianship2->id,
            'email' => 'invalid-email@bounced.com',
        ]);

        // 3. Active - Currently active with future trigger
        $custodianship3 = Custodianship::create([
            'user_id' => $user->id,
            'name' => 'Will and Testament',
            'status' => 'active',
            'delivery_status' => null,
            'interval' => 'P180D',
            'activated_at' => now()->subDays(30),
            'last_reset_at' => now()->subDays(5),
            'next_trigger_at' => now()->addDays(175),
        ]);

        CustodianshipMessage::create([
            'custodianship_id' => $custodianship3->id,
            'content' => 'This message contains my last will and testament, along with instructions for my estate executor.',
        ]);

        Recipient::create([
            'custodianship_id' => $custodianship3->id,
            'email' => 'executor@example.com',
        ]);

        Recipient::create([
            'custodianship_id' => $custodianship3->id,
            'email' => 'lawyer@example.com',
        ]);

        // 4. Draft - Not yet activated
        $custodianship4 = Custodianship::create([
            'user_id' => $user->id,
            'name' => 'Investment Portfolio Access (Draft)',
            'status' => 'draft',
            'delivery_status' => null,
            'interval' => 'P60D',
            'activated_at' => null,
            'last_reset_at' => null,
            'next_trigger_at' => null,
        ]);

        CustodianshipMessage::create([
            'custodianship_id' => $custodianship4->id,
            'content' => 'Access credentials and instructions for my investment portfolio. This is still in draft and needs to be reviewed.',
        ]);

        Recipient::create([
            'custodianship_id' => $custodianship4->id,
            'email' => 'financial.advisor@example.com',
        ]);

        $this->command->info('Successfully created 4 custodianships with recipients and messages.');
    }
}
