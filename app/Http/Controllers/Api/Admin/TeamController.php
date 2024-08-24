<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeamResource;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TeamController extends Controller
{
 /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //get teams
        $teams = Team::when(request()->search, function ($teams) {
            $teams = $teams->where('name', 'like', '%' . request()->search . '%');
        })->latest()->paginate(5);

        //append query string to pagination links
        $teams->appends(['search' => request()->search]);

        //return with Api Resource
        return new TeamResource(true, 'List Data Teams', $teams);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image'    => 'required|mimes:jpeg,jpg,png|max:2000',
            'name'     => 'required',
            'role'     => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //upload image
        $image = $request->file('image');
        $image->storeAs('public/teams', $image->hashName());

        //create team
        $team = Team::create([
            'image'     => $image->hashName(),
            'name'      => $request->name,
            'role'      => $request->role,
        ]);

        if ($team) {
            //return success with Api Resource
            return new TeamResource(true, 'Data Team Berhasil Disimpan!', $team);
        }

        //return failed with Api Resource
        return new TeamResource(false, 'Data Team Gagal Disimpan!', null);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $team = Team::whereId($id)->first();

        if ($team) {
            //return success with Api Resource
            return new TeamResource(true, 'Detail Data Team!', $team);
        }

        //return failed with Api Resource
        return new TeamResource(false, 'Detail Data Team Tidak Ditemukan!', null);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Team $team)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'role'     => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //check image update
        if ($request->file('image')) {

            //remove old image
            Storage::disk('local')->delete('public/teams/' . basename($team->image));

            //upload new image
            $image = $request->file('image');
            $image->storeAs('public/teams', $image->hashName());

            //update team with new image
            $team->update([
                'image' => $image->hashName(),
                'name'  => $request->name,
                'role'  => $request->role,
            ]);
        }

        //update team without image
        $team->update([
            'name' => $request->name,
            'role' => $request->role,
        ]);

        if ($team) {
            //return success with Api Resource
            return new TeamResource(true, 'Data Team Berhasil Diupdate!', $team);
        }

        //return failed with Api Resource
        return new TeamResource(false, 'Data Team Gagal Diupdate!', null);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Team $team)
    {
        //remove image
        Storage::disk('local')->delete('public/teams/' . basename($team->image));

        if ($team->delete()) {
            //return success with Api Resource
            return new TeamResource(true, 'Data Team Berhasil Dihapus!', null);
        }

        //return failed with Api Resource
        return new TeamResource(false, 'Data Team Gagal Dihapus!', null);
    }
}
