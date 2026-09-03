<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiEnvelopeTest extends TestCase
{
    /**
     * Une route API inexistante renvoie l'enveloppe JSON standardisée en 404.
     */
    public function test_unknown_api_route_returns_standard_envelope(): void
    {
        $response = $this->getJson('/api/route-inexistante');

        $response->assertStatus(404);
        $response->assertHeader('content-type', 'application/json');
        $response->assertExactJson([
            'success' => false,
            'message' => 'Route introuvable',
            'data' => null,
            'errors' => null,
        ]);
    }
}
