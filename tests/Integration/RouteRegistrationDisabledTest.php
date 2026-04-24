<?php

namespace VormiaPHP\Vormia\Tests\Integration;

use VormiaPHP\Vormia\Tests\IntegrationTestCase;

class RouteRegistrationDisabledTest extends IntegrationTestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('vormia.register_routes.api', false);
    }

    public function test_api_routes_not_registered_when_disabled(): void
    {
        $response = $this->getJson('/api/vrm/roles');

        $response->assertNotFound();
    }
}
