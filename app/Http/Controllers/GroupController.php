<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Message;
use App\Models\Group;
use App\Events\NewGroupCreated;
use App\Events\JoinGroup;
use App\Events\NewMessage;
use App\Events\DeleteGroup;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

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

        //Session::flash('message','Group Created');
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



        return view('group',['group'=>$group]);
    }

    public function getMessages(Group $group){
        $messages= $group->messages()->with('user')->orderBy('updated_at')->get()->groupBy(function($data){
            return Carbon::parse($data->updated_at)->format('d, F Y');
            //return Carbon::parse($data->created_at)->format('M Y');

        });
        return $messages->toJson();
       //return response()->json($group->messages()->with('user')->get());
    }

    public function sendMessage(Request $request,Group $group){

        if ($request->file('file')) {
              // $image= request('file');
              // $imagePath= "chats".time().".".$image->getClientOriginalExtension();
              // $fullPath= url('/').'/storage/chats/'.$imagePath;
              // $image->storeAs('public/chats',$imagePath);
              
              $path = Storage::disk('s3')->put('chats/pics', $request->file);
               
              $message = $group->messages()->create([

                 'image' => $path,

                 'user_id' => Auth::user()->id

             ]);
        }
        else{
            $message = $group->messages()->create([

                 'content' => $request->content,

                 'user_id' => Auth::user()->id

            ]);

        }

        
            // $NewMessage = Message::where('id', $message->id)->first();


            // broadcast(new NewMessage($NewMessage))->toOthers();

            // return $NewMessage->toJson();


    }

    public  function delete(Group  $group){

        if (Auth::user()->cannot('delete', $group)) {
            abort(403);
        }

        event(new DeleteGroup($group));



        $group->delete();

        return back();        
    
    }


 
}
