<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Message;
use App\Models\Group;
use App\Events\NewGroupCreated;
use App\Events\joinGroup;
use App\Events\NewMessage;

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

    public function join($roomId){

        $group=Group::findorfail($roomId);
        
        $user= Auth::user();

        event(new JoinGroup($user,$roomId));
        
        //broadcast(new NewComment($comment))->toOthers();

        return view('group',['group'=>$group]);
    }

    public function getMessages(Group $group){
       
       return response()->json($group->messages()->with('user')->get());
    }

    public function sendMessage(Request $request,Group $group){
         $message = $group->messages()->create([
            'content' => $request->content,
            'user_id' => Auth::user()->id
        ]);

        $NewMessage = Message::where('id', $message->id)->with('user')->first();
        //broadcast(new NewComment($comment))->toOthers();
        return $NewMessage->toJson();
    }


}
