@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card">
                
                <div class="card-header">
                    <span>Group Subject : <b>{{$group->name}}</b></span>
                </div>

                <div class="card-body" style="max-height:450px;overflow-y:auto;">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                <div id="messages">
 
                </div>


                       
                </div>
            </div>
            <div style="margin-top:20px;">
                <textarea class="form-control" rows="3" name="body" placeholder="Send Message" id="messageBox" v-model="messageBox"></textarea>
                 <button class="btn btn-success" style="margin-top:10px" v-on:click="
        sendMessage">Send</button>

                
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
        newMessage:{}
    },
    mounted(){
        this.getMessages()
        this.joinRoom()
    },
    methods:{
        getMessages(){
            axios.get(`/group/${this.group.id}`)
                
                .then( (response)=>{
                   
                   this.messages=response.data

                   for (var i = 0; i < this.messages.length; i++) {

                        if (this.LoggedInUser.id == this.messages[i].user.id) {
                        
                             $("#messages").append(
                                '<div class="message mt-3 "><span class="ml-2"><b>You</b></span> <span style="color: #aaa;margin-left: 40px;"> '+this.messages[i].created_at+'</span><div class="box"><span>'+this.messages[i].content+'</span></div></div>');
                   
                        }
                   
                        else{
                        

                            $("#messages").append(
                                '<div class="message mt-3"><span class="ml-2">'+this.messages[i].user.name+'</span> <span style="color: #aaa;margin-left: 40px;"> '+this.messages[i].created_at+'</span><div class="box"><span>'+this.messages[i].content+'</span></div></div>');
                        }  
                   
                    }

                    
                }) 
                .catch( function (error){
                  console.log(error);
                })

            },

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

        sendMessage(){
              axios.post(`/group/${this.group.id}`, {
                 content:this.messageBox 
              })  
              .then( (response)=>{
                 
                 this.newMessage=response.data
                 
                 $("#messages").append(
                    '<div class="message mt-3 "><span class="ml-2"><b>You</b></span> <span style="color: #aaa;margin-left: 40px;"> '+this.newMessage.created_at+'</span><div class="box"><span>'+this.newMessage.content+'</span></div></div>');

                  this.messageBox=''


                })

              .catch( function (error){
                  console.log(error);
              })
              
            }, 

    }
});



</script>
@endsection
