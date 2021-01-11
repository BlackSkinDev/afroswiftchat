<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Group;
use App\Events\NewGroupCreated;


class GroupController extends Controller
{
    public function getCreate(){
        return view('create');
    }

    public function postCreate(Request $request){
        $this->validate($request,[
            "subject"=>'required'
        ]);

        $group= Auth::user()->groups()->create([
            "name"=>$request['subject']
        ]);

        event(new NewGroupCreated($group));

        Session::flash('message','Group Created');
        return back();
    }

    public function getGroups(){
        $groups=Group::all();
        return $groups->toJson();
    }
}
