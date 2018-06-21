<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StopRoute extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Relatioships to always load with the model.
     *
     * @var array
     */
    public $with = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'longitude' => 'float',
        'latitude' => 'float',
    ];

    /**
     * Handles the model boot options.
     *
     * @return void
     */
    public static function boot()
    {
        // auto-update route order on creation
        self::creating(function ($route) {
            if (empty($route->order)) {
                $route->order = self::getNextOrder($route->stop_id, $route->next_stop_id);
            }
        });
    }

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    public function stop()
    {
        return $this->belongsTo(TourStop::class);
    }

    /**
     * A stop has one next stop.
     *
     * @return void
     */
    public function nextStop()
    {
        return $this->hasOne(TourStop::class, 'id', 'next_stop_id');
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    // **********************************************************
    // OTHER METHODS
    // **********************************************************

    /**
     * Returns the next free number in the order sequence
     * for the given Stop/NextStop order
     *
     * @return void
     */
    public static function getNextOrder($stopId, $nextId)
    {
        return self::where('stop_id', $stopId)
            ->where('next_stop_id', $nextId)
            ->select(\DB::raw('coalesce(max(`order`), 0) as max_order'))
            ->get()
            ->first()
            ->max_order + 1;
    }
}
