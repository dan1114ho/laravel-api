<?php

namespace Tests\Feature\Cms;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use App\Tour;

class ManageToursTest extends TestCase
{
    use DatabaseMigrations;
    use AttachJwtToken;

    public $tour;
    public $business;

    public function setUp()
    {
        parent::setUp();

        $this->business = createUser('business');

        $this->tour = create('App\Tour', ['user_id' => $this->business->id]);
    }

    /**
     * Helper to provide route to the class tour based on named routes.
     *
     * @param String $name
     * @return void
     */
    public function tourRoute($name)
    {
        return route("cms.tours.$name", $this->tour->id);
    }

    /** @test */
    public function a_tour_requires_a_title_description_and_proper_types_to_be_updated()
    {
        $this->loginAs($this->business);

        $this->updateTour(['title' => null])->assertStatus(422);
        $this->updateTour(['description' => null])->assertStatus(422);
        $this->updateTour(['pricing_type' => null])->assertStatus(422);
        $this->updateTour(['type' => null])->assertStatus(422);
    }

    /** @test */
    public function a_tour_can_be_updated_by_its_creator()
    {
        $this->loginAs($this->business);

        $this->updateTour([
            'title' => 'test title',
            'description' => 'test desc',
            'pricing_type' => Tour::$PRICING_TYPES[0],
            'type' => Tour::$TOUR_TYPES[0],
            ])->assertStatus(200)
            ->assertSee('test title')
            ->assertSee('test desc');
    }

    /** @test */
    public function a_tour_cannot_be_updated_by_another_user()
    {
        $this->signIn('business');

        $this->updateTour([
            'title' => 'new title',
            ])
            ->assertStatus(403);
    }

    protected function updateTour($overrides = [])
    {
        $data = array_merge($this->tour->toArray(), $overrides);

        return $this->json('PATCH', route('cms.tours.update', $this->tour->id), $data);
    }

    /** @test */
    public function a_user_can_get_a_list_of_only_their_tours()
    {
        $this->withExceptionHandling();

        $otherTour = create('App\Tour', ['title' => md5('unique title string')]);

        $this->assertCount(2, Tour::all());

        $this->loginAs($this->business);

        $this->json('GET', route('cms.tours.index'))
            ->assertStatus(200)
            ->assertSee($this->tour->title)
            ->assertDontSee($otherTour->title);
    }

    /** @test */
    public function a_tour_can_be_deleted_by_its_creator()
    {
        $this->loginAs($this->business);

        $this->assertCount(1, $this->business->tours);

        $this->json('DELETE', route('cms.tours.destroy', $this->tour->id))
            ->assertStatus(204);

        $this->assertCount(0, $this->business->fresh()->tours);
    }

    /** @test */
    public function a_tour_cannot_be_deleted_by_another_user()
    {
        $this->signIn('business');

        $this->assertCount(1, $this->business->tours);

        $this->json('DELETE', route('cms.tours.destroy', $this->tour->id))
            ->assertStatus(403);

        $this->assertCount(1, $this->business->fresh()->tours);
    }

    /** @test */
    public function a_tour_can_be_seen_by_its_creator()
    {
        $this->loginAs($this->business);

        $this->json('GET', route('cms.tours.show', $this->tour->id))
            ->assertStatus(200)
            ->assertSee($this->tour->title);
    }

    /** @test */
    public function a_tour_cannot_be_seen_by_another_user()
    {
        $this->signIn('business');

        $this->json('GET', route('cms.tours.show', $this->tour->id))
            ->assertStatus(403);
    }

    /** @test */
    public function a_user_can_update_a_tours_address()
    {
        $this->loginAs($this->business);

        $data = [
            'address1' => md5('123 Elm St.'),
            'address2' => md5('APT 805'),
            'city' => md5('New York'),
            'state' => 'NY',
            'zipcode' => '10001',
        ];

        $this->updateTour($data)
            ->assertStatus(200)
            ->assertSee($data['address1'])
            ->assertSee($data['address2'])
            ->assertSee($data['city'])
            ->assertSee($data['state'])
            ->assertSee($data['zipcode']);
    }

    /** @test */
    public function a_user_can_update_the_tours_social_url()
    {
        $this->loginAs($this->business);

        $this->updateTour([
            'facebook_url' => 'fb_name',
            'twitter_url' => 'twitter_name',
            'instagram_url' => 'insta_name',
        ])->assertStatus(200);

        $t = $this->tour->fresh();
        $this->assertEquals($t->facebook_url, 'fb_name');
        $this->assertEquals($t->twitter_url, 'twitter_name');
        $this->assertEquals($t->instagram_url, 'insta_name');
    }

    /** @test */
    public function a_tours_video_url_requires_a_valid_youtube_url()
    {
        $this->loginAs($this->business);

        $url = 'https://www.youtube.com/watch?v=abcd1234';

        $this->updateTour(['video_url' => $url])
            ->assertStatus(200)
            ->assertJson(['video_url' => $url]);

        $this->assertEquals($url, $this->tour->fresh()->video_url);

        $this->updateTour(['video_url' => 'https://www.google.com/'])
            ->assertStatus(422)
            ->assertSee('video_url');

        $this->updateTour(['video_url' => 'not a url'])
            ->assertStatus(422)
            ->assertSee('video_url');
    }

    /** @test */
    public function a_tours_text_fields_can_be_updated()
    {
        $this->loginAs($this->business);

        $updates = [
            'prize_details' => 'details',
            'prize_instructions' => 'instructions',
            'start_message' => 'starting message',
            'end_message' => 'end message',
        ];

        $this->updateTour($updates)
            ->assertStatus(200)
            ->assertJson($updates);
    }

    /** @test */
    public function a_tour_can_have_a_start_point()
    {
        $this->loginAs($this->business);

        $stop = create('App\TourStop', ['tour_id' => $this->tour]);

        $updates = [
            'start_point' => $stop->id,
        ];

        $this->updateTour($updates)
            ->assertStatus(200)
            ->assertJson($updates);
    }

    /** @test */
    public function a_start_point_must_be_a_stop_on_the_tour()
    {
        $this->loginAs($this->business);

        $otherTour = create('App\Tour', ['user_id' => $this->business->id]);
        $stop = create('App\TourStop', ['tour_id' => $otherTour]);

        $this->updateTour(['start_point' => $stop->id])
            ->assertStatus(422)
            ->assertSee('The selected start point is invalid');
    }

    /** @test */
    public function a_tour_can_have_an_endpoint()
    {
        $this->loginAs($this->business);

        $stop = create('App\TourStop', ['tour_id' => $this->tour]);

        $updates = [
            'end_point' => $stop->id,
        ];

        $this->updateTour($updates)
            ->assertStatus(200)
            ->assertJson($updates);
    }

    /** @test */
    public function a_end_point_must_be_a_stop_on_the_tour()
    {
        $this->loginAs($this->business);

        $otherTour = create('App\Tour', ['user_id' => $this->business->id]);
        $stop = create('App\TourStop', ['tour_id' => $otherTour]);

        $this->updateTour(['end_point' => $stop->id])
            ->assertStatus(422)
            ->assertSee('The selected end point is invalid');
    }
}