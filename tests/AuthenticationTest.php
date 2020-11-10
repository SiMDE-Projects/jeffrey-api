<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class AuthenticationTest extends TestCase
{
    /**
     * Tests the authentication process
     *
     * @return void
     */
    public function testCasLogin()
    {
        $response = $this->json('POST', '/auth/login', [
            'username' => env('TEST_CAS_USER'),
            'password' => env('TEST_CAS_PASSWORD')
        ]);

        $response->assertResponseOk();
        $response->seeJsonContains(['token_type' => 'bearer']);
        $this->seeInDatabase('users', ['username' => env('TEST_CAS_USER')]);;
    }

    public function testGetUser()
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)
            ->json('GET', '/auth/me');

        $response->assertResponseOk();
        $response->seeJson($user->toArray());
    }
}
