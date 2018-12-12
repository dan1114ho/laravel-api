<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\TourResource;
use App\Tour;
use App\Http\Requests\CreateTourRequest;
use App\Http\Controllers\TourController as BaseTourController;
use App\Http\Requests\Admin\TransferTourRequest;

class TourController extends BaseTourController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function index()
    {
        return TourResource::collection(
            Tour::all()
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateTourRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateTourRequest $request)
    {
        if ($tour = Tour::create($request->validated())) {
            return $this->success("The tour {$tour->name} was created successfully.", new TourResource(
                $tour->fresh()
            ));
        }

        return $this->fail();
    }

    /**
     * Transfer tour ownership to the requested user.
     *
     * @param TransferTourRequest $request
     * @param Tour $tour
     * @return \Illuminate\Http\Response
     */
    public function transfer(TransferTourRequest $request, Tour $tour)
    {
        $tour->update(['user_id' => $request->user_id]);

        return $this->success('Tour was successfully transfered.');
    }
}
