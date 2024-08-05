<?php

namespace Tests\Feature;

use App\Models\DebitCard;
use App\Models\DebitCardTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class DebitCardTransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected DebitCard $debitCard;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->debitCard = DebitCard::factory()->create([
            'user_id' => $this->user->id,
        ]);
        Passport::actingAs($this->user);
    }

    public function testCustomerCanSeeAListOfDebitCardTransactions()
    {
        DebitCardTransaction::factory()->create([
            'debit_card_id' => $this->debitCard->id,
        ]);

        $response = $this->get('/api/debit-card-transactions?debit_card_id=' . $this->debitCard->id);

        $response->assertStatus(200);
        $response->json([
            'debit_card_id' => $this->debitCard->id,
        ]);
    }
    
    public function testCustomerCannotSeeAListOfDebitCardTransactionsOfOtherCustomerDebitCard()
    {
        $otherUser = User::factory()->create();
        $otherDebitCard = DebitCard::factory()->create([
            'user_id' => $otherUser->id,
        ]);
        DebitCardTransaction::factory()->create([
            'debit_card_id' => $otherDebitCard->id,
        ]);

        $response = $this->get('/api/debit-card-transactions?debit_card_id=' . $otherDebitCard->id);

        $response->assertStatus(403);
    }

    public function testCustomerCanCreateADebitCardTransaction()
    {
        $data = [
            'amount' => 1000,
            'description' => 'Test transaction',
            'debit_card_id' => $this->debitCard->id,
            'currency_code' => 'IDR',
        ];
    
        $response = $this->post('/api/debit-card-transactions', $data);
    
        $response->assertStatus(201);
        $response->json($data);
    }
    
    

    public function testCustomerCannotCreateADebitCardTransactionToOtherCustomerDebitCard()
    {
        $otherUser = User::factory()->create();
        $otherDebitCard = DebitCard::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $data = [
            'amount' => 1000,
            'description' => 'Test transaction',
            'debit_card_id' => $otherDebitCard->id,
        ];

        $response = $this->post('/api/debit-card-transactions', $data);

        $response->assertStatus(403);
    }

    public function testCustomerCanSeeADebitCardTransaction()
    {
        $transaction = DebitCardTransaction::factory()->create([
            'debit_card_id' => $this->debitCard->id,
        ]);

        $response = $this->get('/api/debit-card-transactions/' . $transaction->id);

        $response->assertStatus(200);
        $response->json([
            'id' => $transaction->id,
            'debit_card_id' => $this->debitCard->id,
        ]);
    }

    public function testCustomerCannotSeeADebitCardTransactionAttachedToOtherCustomerDebitCard()
    {
        $otherUser = User::factory()->create();
        $otherDebitCard = DebitCard::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $transaction = DebitCardTransaction::factory()->create([
            'debit_card_id' => $otherDebitCard->id,
        ]);

        $response = $this->get('/api/debit-card-transactions/' . $transaction->id);

        $response->assertStatus(403);
    }
}
