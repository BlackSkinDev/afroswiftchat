<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    public function getCreate(){
        return view('create');
    }

    public function postCreate(Request $request){
        $this->validate($request,[
            "subject"=>'required'
        ]);
        if (Auth::user()->groups()->create([
            "name"=>$request['subject']
        ])){
            Session::flash('message','Group Created');
            return back();
        }
    }
}
