<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AuthenticationTest extends TestCase
{
    use \Laravel\Lumen\Testing\DatabaseMigrations;

    public function testCasProvider()
    {
        $this->runDatabaseMigrations();
        $base = 'https://' . config('services.cas.endpoint') . config('services.cas.path');

        // Get TGT
        $response = Http::asForm()->post($base . '/v1/tickets', [
            'username' => env('TEST_CAS_USER'),
            'password' => env('TEST_CAS_PASSWORD')
        ]);
        $tgt = $response->header('Location');

        // Get ST
        $response = Http::asForm()->post($tgt, ['service' => config('services.cas.service')]);
        $st = $response->body();

        Auth::attempt(['service' => config('services.cas.service'), 'ticket' => $st]);

        $this->seeInDatabase('users', ['username' => env('TEST_CAS_USER')]);;
    }

    public function testCasLogin()
    {
        $base = 'https://' . config('services.cas.endpoint') . config('services.cas.path');

        // Get TGT
        $response = Http::asForm()->post($base . '/v1/tickets', [
            'username' => env('TEST_CAS_USER'),
            'password' => env('TEST_CAS_PASSWORD')
        ]);
        $tgt = $response->header('Location');

        // Get ST
        $response = Http::asForm()->post($tgt, ['service' => config('services.cas.service')]);
        $st = $response->body();

        dd($st);

        $response = $this->json('POST', '/auth/login', [
            'service' => config('services.cas.service'),
            'ticket' => $st
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
        $token = Auth::fromUser($user);

        $response = $this->json('GET', '/auth/refresh', [], ['Authorization' => 'Bearer ' . $token]);

        $response->assertResponseOk();
        $response->seeJson($user->toArray());
    }
}
