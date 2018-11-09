<?php

namespace App\Mobile\Controllers;

use App\Http\Controllers\Controller;
use App\Mobile\Resources\LeaderboardCollection;
use App\ScoreCard;
use App\Tour;

class LeaderboardController extends Controller
{
    /**
     * Get the leaderboard for a given Tour.
     *
     * @param Tour $tour
     * @return LeaderboardCollection
     */
    public function show(Tour $tour)
    {
        $scores = ScoreCard::with(['user'])
            ->where('tour_id', modelId($tour))
            ->orderBy('points', 'desc')
            ->where(function ($query) {
                return $query->where(function ($q) {
                    return $q->forAdventures()
                          ->finished();
                })
                ->orWhere(function ($q) {
                    return $q->forRegularTours();
                });
            })
            ->limit(100)
            ->get();

        return new LeaderboardCollection($scores);
    }
}
