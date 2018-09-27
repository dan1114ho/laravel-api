<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Concerns\AttachJwtToken;
use App\Tour;
use App\Review;

class TourReviewsTest extends TestCase
{
    use DatabaseMigrations, AttachJwtToken;

    protected $tour;
    protected $user;

    public function setUp()
    {
        parent::setUp();

        $this->signIn('user');
        $this->user = $this->signInUser;

        $this->tour = factory(Tour::class)->states('published')->create();
    }

    /** @test */
    public function a_user_can_submit_a_tour_review()
    {
        $this->withoutExceptionHandling();

        $this->assertCount(0, $this->tour->reviews);

        $data = factory(Review::class)->make(['user_id' => $this->user->id])->toArray();

        $this->postJson(route('mobile.reviews.store', ['tour' => $this->tour]), $data)
            ->assertJsonFragment($data)
            ->assertStatus(200);

        $this->assertCount(1, $this->tour->fresh()->reviews);
    }

    /** @test */
    public function if_a_user_submits_a_tour_twice_it_should_update_the_review()
    {
        $this->assertCount(0, $this->tour->reviews);

        $data = factory(Review::class)->make()->toArray();

        $this->postJson(route('mobile.reviews.store', ['tour' => $this->tour]), $data)
            ->assertStatus(200);

        $this->assertCount(1, $this->tour->fresh()->reviews);

        $data['review'] = 'test';
        $data['rating'] = 50;

        $this->postJson(route('mobile.reviews.store', ['tour' => $this->tour]), $data)
            ->assertStatus(200)
            ->assertJsonFragment(['review' => 'test']);

        $this->assertCount(1, $this->tour->fresh()->reviews);
    }

    /** @test */
    public function a_tours_rating_should_change_with_every_review()
    {
        factory(Review::class)->create(['rating' => 10]);
        factory(Review::class)->create(['rating' => 20]);
        factory(Review::class)->create(['rating' => 30]);

        $this->assertEquals(20, $this->tour->fresh()->rating);

        $data = factory(Review::class)->make(['rating' => 40])->toArray();
        $this->postJson(route('mobile.reviews.store', ['tour' => $this->tour]), $data)
            ->assertStatus(200);

        $this->assertEquals(25, $this->tour->fresh()->rating);
    }

    /** @test */
    public function a_user_cannot_review_an_unpublished_tour()
    {
        $this->tour->update(['published_at' => null]);

        $this->assertCount(0, $this->tour->fresh()->reviews);

        $data = factory(Review::class)->make()->toArray();

        $this->postJson(route('mobile.reviews.store', ['tour' => $this->tour]), $data)
            ->assertStatus(404);

        $this->assertCount(0, $this->tour->fresh()->reviews);
    }
}
