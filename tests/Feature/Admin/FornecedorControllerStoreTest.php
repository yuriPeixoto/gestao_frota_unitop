<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Http\Middleware\RequireTwoFactorAuthentication;
use App\Models\User;

class FornecedorControllerStoreTest extends TestCase
{
    /** @test */
    public function store_should_validate_required_fields_and_not_hit_database()
    {
        // Arrange: build an invalid payload that misses required fields
        // Required by controller validation: nome_fornecedor, id_tipo_fornecedor, email
        $payload = [
            // intentionally empty to trigger validation errors
        ];

        // Authenticate as an in-memory user (no DB hit)
        $user = new User([
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $this->be($user);

        // Ensure 2FA middleware passes (simulate already authenticated 2FA)
        $this->withSession(['two_factor_authenticated' => true]);

        // Act: post to the admin fornecedores store route
        $response = $this->from('/dummy')->post(route('admin.fornecedores.store'), $payload);

        // Assert: redirect back with validation errors for the required fields
        $response->assertStatus(302);
        $response->assertRedirect('/dummy');
        // Note: The controller catches ValidationException and flashes a generic 'error' message.
        $response->assertSessionHas('error');
    }
}
