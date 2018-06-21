<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStopRequest;
use App\Tour;
use App\Http\Resources\StopResource;
use App\TourStop;
use App\Http\Resources\StopCollection;
use App\Http\Requests\UpdateStopRequest;
use App\StopChoice;
use Illuminate\Support\Arr;

class StopController extends Controller
{
    /**
     * Lists all stops for a given tour.
     *
     * @param Tour $tour
     * @return StopCollection
     */
    public function index(Tour $tour)
    {
        return new StopCollection(
            $tour->stops
        );
    }

    /**
     * Stores a new stop for the given tour.
     *
     * @param CreateStopRequest $request
     * @param Tour $tour
     * @return StopResource
     */
    public function store(CreateStopRequest $request, Tour $tour)
    {
        $order = $tour->getNextStopOrder();

        $data = array_merge($request->validated(), ['order' => $order]);

        \DB::beginTransaction();

        if ($stop = $tour->stops()->create(Arr::except($data, ['choices', 'location', 'routes']))) {
            if ($request->has('location')) {
                $stop->location()->update($data['location']);
            }

            $stop->updateChoices($request->choices);

            \DB::commit();

            return $this->success("The stop {$stop->title} was created successfully.", new StopResource(
                $stop->fresh()
            ));
        }

        \DB::rollback();
        return $this->fail();
    }

    /**
     * Gets the details of a given tour stop.
     *
     * @param Tour $tour
     * @param TourStop $stop
     * @return StopResource
     */
    public function show(Tour $tour, TourStop $stop)
    {
        return new StopResource($stop);
    }

    /**
     * Updates a tour stop with the given data.
     *
     * @param UpdateStopRequest $request
     * @param Tour $tour
     * @param TourStop $stop
     * @return StopResource
     */
    public function update(UpdateStopRequest $request, Tour $tour, TourStop $stop)
    {
        $data = $request->validated();

        \DB::beginTransaction();

        if ($stop->update(Arr::except($data, ['choices', 'location', 'routes']))) {
            if ($request->has('location')) {
                $stop->location()->update($data['location']);
            }

            $stop->updateChoices($request->choices);

            $stop->syncRoutes($request->routes);

            \DB::commit();

            return $this->success("{$stop->title} was updated successfully.", new StopResource($stop->fresh()));
        }

        \DB::rollback();
        return $this->fail();
    }

    /**
     * Deletes the given tour stop.
     *
     * @param Tour $tour
     * @param TourStop $stop
     * @return Response
     */
    public function destroy(Tour $tour, TourStop $stop)
    {
        if (StopChoice::where('next_stop_id', $stop->id)->count() > 0) {
            return $this->fail(422, 'You cannot delete this stop because it is referenced in another stops destination points.');
        }

        if (Tour::where('start_point_id', $stop->id)->count() > 0) {
            return $this->fail(422, 'You cannot delete this stop because it set as the Tour\'s start point.');
        }

        if (Tour::where('end_point_id', $stop->id)->count() > 0) {
            return $this->fail(422, 'You cannot delete this stop because it set as the Tour\'s end point.');
        }

        if ($stop->delete()) {
            return $this->success("{$stop->title} was archived successfully.");
        }

        return $this->fail();
    }

    /**
     * Sets the order of the given tour stop.
     *
     * @param Tour $tour
     * @param TourStop $stop
     * @return StopCollection
     */
    public function changeOrder(Tour $tour, TourStop $stop)
    {
        request()->validate([
            'order' => 'required|numeric'
        ]);

        $stop->order = abs(request()->order);

        $tour->increaseOrderAt($stop->order);

        if ($stop->save()) {
            return $this->success(
                "{$tour->title}'s stop order was updated successfully.",
                new StopCollection($tour->stops()->get())
            );
        }

        return $this->fail();
    }
}
