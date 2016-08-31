<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Net;
use Auth;
use App\Http\Requests;
use Illuminate\Support\Facades\Validator;

class NetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $nets = Net::all();
        return view('net.index', ['nets'=>$nets]);
    }

    public function edit($id)
    {
        $net = Net::findOrFail($id);
        return view('net.edit', ['net'=>$net]);
    }

    public function create()
    {
        return view('net.create');
    }

    function getElement($products, $field, $value)
    {
        foreach($products as $key => $product)
        {
            if ( $product[$field] === $value )
                return $key;
        }
        return false;
    }

    public function canFire($t, $places){
        $fire = true;
        foreach($t['ps'] as $ps) {

            $key = $this->getElement($places, 'pId', $ps['pId']);

            if ($ps['qty'] > $places[$key]['tokens']) {
                $fire = false;
            }
        }

        return $fire;
    }

    public function fireTransition($t, $places){
        if($this->canFire($t, $places)){
            foreach($t['ps'] as $ps){
                $key = $this->getElement($places, 'pId', $ps['pId']);
                $places[$key]['tokens'] = $places[$key]['tokens'] - $ps['qty'];
            }

            foreach($t['pt'] as $pt){
                $key = $this->getElement($places, 'pId', $pt['pId']);
                $places[$key]['tokens'] = $places[$key]['tokens'] + $pt['qty'];
            }
        }
        return $places;
    }

    public function fireRandTransition($transitions, $places){
        $toFire = [];
        foreach ($transitions as $t){

            if($this->canFire($t, $places)){

                array_push($toFire, $t);
            }
        }



        if(count($toFire)>0){
            $tk = array_rand($toFire, 1);
            $places = $this->fireTransition($toFire[$tk], $places);
        }


        return $places;
    }


    public function simulate(Request $request){
        $transitions = $request->input('transitions');
        $places = $request->input('places');
        for($i = 0; $i< $request->input('steps'); $i++)
        {

            $places = $this->fireRandTransition($transitions, $places);
        }

        return response()->json($places);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
        ]);

        if($validator->fails()) {
            return redirect(route('net.create'))->withInput()->withErrors($validator);
        }

        $net = new Net();
        $net->name =  $request->input('name');
        $net->owner = Auth::user()->id;

        $net->save();

        return redirect(route('net.index'));
    }
}
