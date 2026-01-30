<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TicketControllerTest extends WebTestCase
{
    private function createAuthenticatedUser(): string
    {
        $client = static::createClient();

        $client->request('POST', '/api/register', [], [], 
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'ticket' . time() . rand() . '@test.com',
                'password' => 'password123'
            ])
        );

        $data = json_decode($client->getResponse()->getContent(), true);
        return $data['data']['token'];
    }

    public function testCreateTicket(): void
    {
        $token = $this->createAuthenticatedUser();
        static::ensureKernelShutdown();
        
        $client = static::createClient();

        $client->request('POST', '/api/tickets', [], [], 
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token
            ],
            json_encode([
                'title' => 'Test ticket',
                'description' => 'Description du test',
                'priority' => 'HIGH'
            ])
        );

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('success', $data['message']);
        $this->assertArrayHasKey('ticket', $data['data']);
    }

    public function testListTickets(): void
    {
        $token = $this->createAuthenticatedUser();
        static::ensureKernelShutdown();
        
        $client = static::createClient();

        $client->request('GET', '/api/tickets', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token
        ]);

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('success', $data['message']);
        $this->assertArrayHasKey('tickets', $data['data']);
        $this->assertArrayHasKey('total', $data['meta']);
    }

    public function testCreateTicketWithoutAuth(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/tickets', [], [], 
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'Test', 'description' => 'Test'])
        );

        $this->assertResponseStatusCodeSame(401);
    }

    public function testListTicketsWithFilters(): void
    {
        $token = $this->createAuthenticatedUser();
        static::ensureKernelShutdown();
        
        $client = static::createClient();

        // Créer des tickets avec différents status et priorités
        $client->request('POST', '/api/tickets', [], [], 
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token
            ],
            json_encode(['title' => 'High priority', 'description' => 'Test', 'priority' => 'HIGH'])
        );

        static::ensureKernelShutdown();
        $client = static::createClient();

        // Tester le filtre par priorité
        $client->request('GET', '/api/tickets?priority=HIGH', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token
        ]);

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('success', $data['message']);
        $this->assertGreaterThanOrEqual(1, count($data['data']['tickets']));
    }

    public function testListTicketsWithSort(): void
    {
        $token = $this->createAuthenticatedUser();
        static::ensureKernelShutdown();
        
        $client = static::createClient();

        // Créer 2 tickets
        $client->request('POST', '/api/tickets', [], [], 
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token
            ],
            json_encode(['title' => 'A First', 'description' => 'Test'])
        );

        static::ensureKernelShutdown();
        $client = static::createClient();

        $client->request('POST', '/api/tickets', [], [], 
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token
            ],
            json_encode(['title' => 'B Second', 'description' => 'Test'])
        );

        static::ensureKernelShutdown();
        $client = static::createClient();

        // Tester le tri par titre ASC
        $client->request('GET', '/api/tickets?sort=title&order=ASC', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token
        ]);

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('success', $data['message']);
        $tickets = $data['data']['tickets'];
        if (count($tickets) >= 2) {
            $this->assertLessThanOrEqual($tickets[1]['title'], $tickets[0]['title']);
        }
    }

    public function testUpdateTicketStatus(): void
    {
        $token = $this->createAuthenticatedUser();
        static::ensureKernelShutdown();
        
        $client = static::createClient();

        // Créer un ticket
        $client->request('POST', '/api/tickets', [], [], 
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token
            ],
            json_encode(['title' => 'Test update', 'description' => 'Test'])
        );

        $data = json_decode($client->getResponse()->getContent(), true);
        $ticketId = $data['data']['ticket']['id'];

        static::ensureKernelShutdown();
        $client = static::createClient();

        // Mettre à jour le status
        $client->request('PATCH', '/api/tickets/' . $ticketId, [], [], 
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token
            ],
            json_encode(['status' => 'IN_PROGRESS'])
        );

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('IN_PROGRESS', $data['data']['ticket']['status']);
    }

    public function testDeleteTicket(): void
    {
        $token = $this->createAuthenticatedUser();
        static::ensureKernelShutdown();
        
        $client = static::createClient();

        // Créer un ticket
        $client->request('POST', '/api/tickets', [], [], 
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token
            ],
            json_encode(['title' => 'Test delete', 'description' => 'Test'])
        );

        $data = json_decode($client->getResponse()->getContent(), true);
        $ticketId = $data['data']['ticket']['id'];

        static::ensureKernelShutdown();
        $client = static::createClient();

        // Supprimer le ticket
        $client->request('DELETE', '/api/tickets/' . $ticketId, [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testUpdateTicketInvalidTransition(): void
    {
        $token = $this->createAuthenticatedUser();
        static::ensureKernelShutdown();
        
        $client = static::createClient();

        // Créer un ticket (status OPEN par défaut)
        $client->request('POST', '/api/tickets', [], [], 
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token
            ],
            json_encode(['title' => 'Test', 'description' => 'Test'])
        );

        $data = json_decode($client->getResponse()->getContent(), true);
        $ticketId = $data['data']['ticket']['id'];

        static::ensureKernelShutdown();
        $client = static::createClient();

        // Passer directement de OPEN à DONE devrait échouer selon canTransitionTo
        // Mais on permet OPEN -> DONE maintenant, donc on teste autre chose
        // Mettons le ticket à DONE puis essayons de le réouvrir
        $client->request('PATCH', '/api/tickets/' . $ticketId, [], [], 
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token
            ],
            json_encode(['status' => 'DONE'])
        );

        static::ensureKernelShutdown();
        $client = static::createClient();

        // Essayer de revenir à OPEN (impossible depuis DONE)
        $client->request('PATCH', '/api/tickets/' . $ticketId, [], [], 
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token
            ],
            json_encode(['status' => 'OPEN'])
        );

        $this->assertResponseStatusCodeSame(400);
    }
}
