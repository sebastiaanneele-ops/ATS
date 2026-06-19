<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * De homepage stuurt door naar het beheerpaneel.
     */
    public function test_the_application_redirects_root_to_admin(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/admin');
    }
}
