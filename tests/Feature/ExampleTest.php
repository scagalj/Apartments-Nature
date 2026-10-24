<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_root_redirects_to_browser_language()
    {
        $this->get('/', ['Accept-Language' => 'de-DE,de;q=0.9'])
            ->assertStatus(302)
            ->assertRedirect('/de');
    }

    public function test_root_falls_back_to_english()
    {
        $this->get('/', ['Accept-Language' => 'ja'])->assertRedirect('/en');
    }

    public function test_unknown_locale_is_not_found()
    {
        $this->get('/foo')->assertNotFound();
        $this->get('/xx/contactus')->assertNotFound();
    }
}
