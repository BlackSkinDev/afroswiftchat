@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card">
                
                <div class="card-header">
                    <span>Group Subject : <b>{{$group->name}}</b></span>
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                       
                </div>
            </div>
            <div style="margin-top:20px;">
                <textarea class="form-control" rows="3" name="body" placeholder="Send Message" id="messageBox"></textarea>
                <button class="btn btn-success" style="margin-top:10px">Send</button>
            </div>

        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-header"> {{Auth::user()->name}}, Send Messages to chat room</div>

                <div class="card-body">
                    <div class="alert alert-success" id="popUp" style="display: none;"></div>
                    <div class="alert alert-danger" id="popUp2" style="display: none;"></div>
                    
                    <h4>Current users</h4>
                    <div id="users" > 
                        
                    </div>
                    
                </div>
            </div>
       
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/app.js') }}" ></script>
<script>

const app = new Vue({
    el: '#app',
    data:{
        group:{!! $group->toJson() !!},
        LoggedInUser:{!! Auth::check() ? Auth::user()->toJson(): 'null' !!},
        messages:{},
        messageBox:'',
    },
    mounted(){
        
        this.joinRoom()
    },
    methods:{

        joinRoom(){
           Echo.join('chatroom.'+this.group.id)
             .here((user) => {
                 for (var i = 0; i < user.length; i++) {
                    if (this.LoggedInUser.id==user[i].id) {
                        $("#users").append('<p id='+user[i].id+'><b>You: </b>'+user[i].name+'</p>');
                    }
                    else{
                        $("#users").append('<p id='+user[i].id+'>'+user[i].name+'</p>');    
                    }
                    
                }
               
             })
            .joining((user) => {
                console.log(user.name+ " joined")
                $("#users").append('<p id='+user.id+'>'+user.name+'</p>');

                $("#popUp").text(user.name+ " Joined");

                $( "#popUp" ).show(); 

                setTimeout(function() {

                    $( "#popUp" ).hide();

                }, 3000);
            })
            .leaving((user) => {
                console.log(user.name + "left");
                $("#"+user.id).remove();
                $("#popUp2").text(user.name+ " Left");

                $( "#popUp2" ).show(); 

                setTimeout(function() {

                    $( "#popUp2" ).hide();

                }, 3000);
                
            });
        },
    }
});

</script>
@endsection
