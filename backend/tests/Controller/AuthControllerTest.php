<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthControllerTest extends WebTestCase
{
    public function testRegisterSuccess(): void
    {
        $client = static::createClient();
        
        $client->request('POST', '/api/register', [], [], 
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'test' . time() . '@example.com',
                'password' => 'password123'
            ])
        );

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertEquals('success', $data['message']);
        $this->assertArrayHasKey('token', $data['data']);
        $this->assertArrayHasKey('user', $data['data']);
        $this->assertArrayHasKey('email', $data['data']['user']);
    }

    public function testRegisterWithExistingEmail(): void
    {
        $client = static::createClient();
        $email = 'duplicate' . time() . '@example.com';

        $client->request('POST', '/api/register', [], [], 
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => $email, 'password' => 'password123'])
        );
        $this->assertResponseIsSuccessful();

        $client->request('POST', '/api/register', [], [], 
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => $email, 'password' => 'password123'])
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('error', $data['message']);
    }

    public function testRegisterValidationFails(): void
    {
        $client = static::createClient();
        
        $client->request('POST', '/api/register', [], [], 
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => 'invalid-email', 'password' => '123'])
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('error', $data['message']);
    }

    public function testLoginSuccess(): void
    {
        $client = static::createClient();
        $email = 'login' . time() . '@example.com';
        $password = 'password123';

        $client->request('POST', '/api/register', [], [], 
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => $email, 'password' => $password])
        );

        $client->request('POST', '/api/login', [], [], 
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => $email, 'password' => $password])
        );

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertEquals('success', $data['message']);
        $this->assertArrayHasKey('token', $data['data']);
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $client = static::createClient();
        
        $client->request('POST', '/api/login', [], [], 
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => 'test@example.com', 'password' => 'test'])
        );

        $this->assertResponseStatusCodeSame(401);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('error', $data['message']);
    }
}
