<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Access\Response;
use Illuminate\Http\Request;
use App\State;
use App\Net;
use Auth;
use App\Http\Requests;

class StateController extends Controller
{

    public function state($net){

        $version = State::where('net_id', $net->id)->max('version');
        return State::where('net_id', $net->id)->where('version', $version)->first();
    }

    public function store(Request $request)
    {

        $net = Net::findOrFail($request->input('net_id'));
        $laststate = $this->state($net);
        if($laststate != null)
            $version = $laststate->version + 1;
        else
            $version = 0;
        $state = new State();
        $state->data = $request->input('data');
        $state->net_id = $request->input('net_id');
        $state->author = Auth::user()->id;
        $state->version = $version;

        $state->save();

        return response()->json("success");
    }

    public function load($net_id){
        $net = Net::findOrFail($net_id);
        $state = $this->state($net);
            return response()->json($state->data);
    }


    
}
