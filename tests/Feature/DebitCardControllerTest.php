<?php

namespace Tests\Feature;

use App\Models\DebitCard;
use App\Models\DebitCardTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class DebitCardControllerTest extends TestCase
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
    

    public function testCustomerCanSeeAListOfDebitCards()
    {
        $response = $this->get('/api/debit-cards');
        $response->assertStatus(200);
        $data = $response->json();
        \Log::info('Response data: ', $data);
    
        $expectedDate = $this->debitCard->expiration_date->format('Y-m-d H:i:s'); 
        $response->json([
            'id' => $this->debitCard->id,
            'number' => (string) $this->debitCard->number,
            'expiration_date' => $expectedDate,
            'type' => $this->debitCard->type,
        ]);
    }
    
    public function testCustomerCannotSeeAListOfDebitCardsOfOtherCustomers()
    {
        $otherUser = User::factory()->create();
        DebitCard::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->get('/api/debit-cards');

        $response->assertStatus(200);
        $response->assertJsonMissing([
            'user_id' => $otherUser->id,
        ]);
    }

    public function testCustomerCanCreateADebitCard()
    {
        $data = [
            'number' => '1234567812345678',
            'expiration_date' => '2025-12-31',
            'cvv' => '123',
            'type' => 'Visa',
        ];

        $response = $this->post('/api/debit-cards', $data);

        $response->assertStatus(201);
        $response->json([
            'number' => $data['number'],
            'expiration_date' => $data['expiration_date'],
            'cvv' => $data['cvv'],
            'type' => $data['type'],
        ]);
    }

    public function testCustomerCanSeeASingleDebitCardDetails()
    {
        $response = $this->get('/api/debit-cards/' . $this->debitCard->id);
    
        $response->assertStatus(200);
        $response->json([
            'id' => $this->debitCard->id,
            'number' => (string) $this->debitCard->number,
            'expiration_date' => $this->debitCard->expiration_date->format('Y-m-d H:i:s'),
            'type' => $this->debitCard->type,
        ]);
    }
    

    public function testCustomerCannotSeeASingleDebitCardDetails()
    {
        $otherUser = User::factory()->create();
        $otherDebitCard = DebitCard::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->get('/api/debit-cards/' . $otherDebitCard->id);

        $response->assertStatus(403);
    }

    public function testCustomerCanActivateADebitCard()
    {
        $response = $this->put('/api/debit-cards/' . $this->debitCard->id, [
            'is_active' => true, 
        ]);

        $response->assertStatus(200);
        $response->json([
            'id' => $this->debitCard->id,
            'is_active' => true, 
        ]);
    }

    public function testCustomerCanDeactivateADebitCard()
    {
        $response = $this->put('/api/debit-cards/' . $this->debitCard->id, [
            'is_active' => false,
        ]);

        $response->assertStatus(200);
        $response->json([
            'id' => $this->debitCard->id,
            'is_active' => false,
        ]);
    }
    
    public function testUpdateDebitCardWithInvalidData()
    {
        $response = $this->put('/api/debit-cards/' . $this->debitCard->id, [
            'is_active' => false,
        ]);
        // dd($response->getContent());
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('is_active');
    }
    
    
    
    public function testCustomerCanDeleteADebitCard()
    {
        $this->assertDatabaseHas('debit_cards', [
            'id' => $this->debitCard->id,
        ]);
    
        $response = $this->delete('/api/debit-cards/' . $this->debitCard->id);
        $response->assertStatus(204);
        $this->assertSoftDeleted('debit_cards', [
            'id' => $this->debitCard->id,
        ]);
    }

    public function testCustomerCannotDeleteADebitCardWithTransaction()
    {
        DebitCardTransaction::factory()->create([
            'debit_card_id' => $this->debitCard->id,
        ]);
    
        $response = $this->delete('/api/debit-cards/' . $this->debitCard->id);
    
        $response->assertStatus(403);
    }
    
}
