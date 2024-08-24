<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeamResource;
use App\Models\Team;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index()
    {
        $teams = Team::oldest()->get();

        //return with Api Resource
        return new TeamResource(true, 'List Data Teams', $teams);
    }
}
