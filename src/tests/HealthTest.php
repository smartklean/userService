<?php

class HealthTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testHealthCheck()
    {
        $this->get('/health');

        $this->seeStatusCode(200);
        $this->seeJsonStructure([
          'status'
        ]);
    }
}
