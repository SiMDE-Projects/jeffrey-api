<?php

namespace App\Auth;

use App\Models\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

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
            $response = Http::asForm()->post($this->getCasEndpoint() . '/v1/users', $credentials);
        } catch (\Exception $e) {
            return null;
        }
        // Check if authentication was successful
        if (!$response->successful()) {
            return null;
        }

        // Get user data
        $model = $this->parseCASResponse($response->json());
        // Update the user accordingly
        return $this->updateModel($model['id'], $model['data']);
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
        return $user->username === $credentials['username'];
    }

    /**
     * Extract user data from a CAS response
     * @param array $response
     * @return array
     */
    protected function parseCASResponse(array $response)
    {
        // If it was, get user attributes
        $attributes = $response['authentication']['principal']['attributes'];

        return [
            'id' => $attributes['uid'][0],
            'data' => [
                'first_name' => $attributes['givenName'][0],
                'last_name' => $attributes['sn'][0],
                'email' => $attributes['mail'][0],
            ]
        ];
    }

    /**
     * Updates a user with data pulled from CAS
     * @param $id
     * @param $data
     * @return User
     */
    protected function updateModel($id, $data)
    {
        if (!$this->model::exists($id)) {
            $model = $this->model::create(array_merge(['username' => $id], $data));
        } else {
            $model = $this->model::find($id);
            $model->update($data);
            $model->fresh();
        }
        return $model;
    }

    /**
     * Retrieves CAS endpoint from configuration
     */
    protected function getCasEndpoint()
    {
        return 'https://' . config('services.cas.endpoint') . config('services.cas.path');
    }
}
