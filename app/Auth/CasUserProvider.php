<?php

namespace App\Auth;

use App\Models\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Support\Facades\Http;

class CasUserProvider extends EloquentUserProvider
{
    /**
     * Retrieve a user by the given credentials.
     *
     * @param array $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        // Match credentials against CAS
        try {
            $response = Http::get($this->getCasEndpoint() . '/p3/serviceValidate', $credentials);
        } catch (\Exception $e) {
            return null;
        }
        // Check if authentication was successful
        if (!$response->successful()) {
            return null;
        }

        // Get user data
        $data = $this->parseCASResponse($response->body());

        // If we didn't get anything from CAS
        if(empty($data)){
            return null;
        }

        // Update the user accordingly
        return $this->updateModel($data, $credentials['ticket']);
    }

    /**
     * Retrieves CAS endpoint from configuration
     */
    protected function getCasEndpoint()
    {
        return 'https://' . config('services.cas.endpoint') . config('services.cas.path');
    }

    /**
     * Extract user data from a CAS response
     * @param array $response
     * @return array
     */
    protected function parseCASResponse($response)
    {
        // If it was, get user attributes
        $xml = (simplexml_load_string($response))->children('cas', true);
        if (!empty($xml->authenticationFailure)) {
            return null;
        }

        $loginResponse = $xml->authenticationSuccess;

        return [
            'username' => (string) $loginResponse->user,
            'first_name' => (string) $loginResponse->attributes->givenName,
            'last_name' => (string) $loginResponse->attributes->sn,
            'email' => (string) $loginResponse->attributes->mail,
        ];
    }

    /**
     * Updates a user with data pulled from CAS
     * @param $id
     * @param $data
     * @return User
     */
    protected function updateModel($data, $ticket)
    {
        $data = array_merge($data, ['service_ticket' => $ticket]);

        if (!$this->model::exists($data['username'])) {
            $model = $this->model::create($data);
        } else {
            $model = $this->model::find($data['username']);
            $model->update($data);
            $model->fresh();
        }

        return $model;
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param array $credentials
     * @return bool
     */
    public function validateCredentials(UserContract $user, array $credentials)
    {
        return $user->service_ticket === $credentials['ticket'];
    }
}
