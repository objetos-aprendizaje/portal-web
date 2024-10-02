<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Saml2TenantsModel>
 */
class Saml2TenantsModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => generate_uuid(),
            'idp_entity_id' => 1234,
            'idp_login_url' =>"",
            'idp_logout_url' =>'',
            'idp_x509_cert' => '',
            'metadata' =>json_encode([]),

        ];
    }
}
