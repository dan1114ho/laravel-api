<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\ClientCollection;
use App\Http\Controllers\Controller;
use App\Client;
use App\Http\Requests\Admin\CreateClientRequest;
use App\Http\Resources\ClientResource;
use App\Http\Requests\Admin\UpdateClientRequest;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ClientCollection(Client::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateClientRequest $request)
    {
        if ($client = Client::create($request->validated())) {
            return $this->success("{$client->name} was added successfully.", new ClientResource($client));
        }

        return $this->fail();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Client $client)
    {
        return new ClientResource($client);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateClientRequest $request, Client $client)
    {
        if ($client->update($request->validated())) {
            $client = $client->fresh();
            return $this->success("{$client->name} was updated successfully.", new ClientResource($client));
        }

        return $this->fail();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Client $client)
    {
        if ($client->delete()) {
            return $this->success("{$client->name} was archived successfully.");
        }

        return $this->fail();
    }
}