<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\TourStop;

class TourTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_can_determine_the_next_order_for_its_stops()
    {
        $client = createUser('client');

        $tour = create('App\Tour', ['user_id' => $client->id]);

        $this->assertEquals(1, $tour->getNextStopOrder());

        create(TourStop::class, ['tour_id' => $tour->id, 'order' => 1]);

        $this->assertEquals(2, $tour->getNextStopOrder());

        create(TourStop::class, ['tour_id' => $tour->id, 'order' => 5]);

        $this->assertEquals(6, $tour->getNextStopOrder());
    }

    /** @test */
    public function it_gets_the_full_facebook_url()
    {
        $client = createUser('client');

        $tour = create('App\Tour', ['user_id' => $client->id]);

        $tour->facebook_url = 'new_social_url';

        $this->assertStringStartsWith('http', $tour->facebook_url_path);
        $this->assertContains('facebook.com', $tour->facebook_url_path);
        $this->assertContains('new_social_url', $tour->facebook_url_path);
    }

    /** @test */
    public function it_gets_the_full_twitter_url()
    {
        $client = createUser('client');

        $tour = create('App\Tour', ['user_id' => $client->id]);

        $tour->twitter_url = 'new_social_url';

        $this->assertStringStartsWith('http', $tour->twitter_url_path);
        $this->assertContains('twitter.com', $tour->twitter_url_path);
        $this->assertContains('new_social_url', $tour->twitter_url_path);
    }

    /** @test */
    public function it_gets_the_full_instagram_url()
    {
        $client = createUser('client');

        $tour = create('App\Tour', ['user_id' => $client->id]);

        $tour->instagram_url = 'new_social_url';

        $this->assertStringStartsWith('http', $tour->instagram_url_path);
        $this->assertContains('instagram.com', $tour->instagram_url_path);
        $this->assertContains('new_social_url', $tour->instagram_url_path);
    }
}
