<?php

class HomeTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testHomeRouteOnLocalEnvironment()
    {
        config(['app.env' => 'local']);

        $this->get('/');

        $this->seeStatusCode(200);
        $this->seeJsonStructure([
          'status',
          'data',
          'message'
        ]);
    }

    public function testHomeRouteOnProductionEnvironment()
    {
        config(['app.env' => 'production']);

        $this->get('/');

        $this->seeStatusCode(200);
        $this->seeJsonStructure([
          'status',
          'message'
        ]);
    }
}
