<?php

use Laravel\Lumen\Testing\WithoutMiddleware;

class TokenValidationTest extends TestCase
{

    use WithoutMiddleware;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testTokenValidation()
    {
        $this->get('/api/v1/user/token/validate');

        $this->seeStatusCode(200);
        $this->seeJsonStructure([
          'status',
        ]);
    }
}
