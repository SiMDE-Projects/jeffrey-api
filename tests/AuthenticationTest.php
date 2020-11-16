<?php

class AuthenticationTest extends TestCase
{
    public function testCasLogin()
    {
        $response = $this->json('POST', '/auth/login', [
            'username' => env('TEST_CAS_USER'),
            'password' => env('TEST_CAS_PASSWORD')
        ]);

        $response->assertResponseOk();
        $response->seeJsonContains(['type' => 'bearer']);
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

    public function testRefreshToken()
    {
        $user = \App\Models\User::factory()->create();
        $token = \Illuminate\Support\Facades\Auth::fromUser($user);

        $response = $this->json('GET', '/auth/refresh', [], ['Authorization' => 'Bearer ' . $token]);

        $response->assertResponseOk();
        $response->seeJson($user->toArray());
    }
}
