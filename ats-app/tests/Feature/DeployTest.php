<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DeployTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        // De deploy-hook draait config:cache/route:cache; opruimen zodat er geen
        // (test-)cache achterblijft voor de lokale omgeving of volgende tests.
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        parent::tearDown();
    }

    public function test_deploy_requires_a_valid_token(): void
    {
        config(['ats.deploy_token' => 'secret-token']);

        $this->get('/__ops/deploy')->assertForbidden();
        $this->get('/__ops/deploy?token=wrong')->assertForbidden();
    }

    public function test_deploy_is_disabled_when_no_token_configured(): void
    {
        config(['ats.deploy_token' => '']);

        $this->get('/__ops/deploy?token=anything')->assertForbidden();
    }

    public function test_deploy_runs_with_a_valid_token(): void
    {
        config(['ats.deploy_token' => 'secret-token']);

        $response = $this->get('/__ops/deploy?token=secret-token');

        $response->assertOk();
        $response->assertSee('[migrate] OK');
    }

    public function test_root_redirects_to_admin(): void
    {
        $this->get('/')->assertRedirect('/admin');
    }
}
