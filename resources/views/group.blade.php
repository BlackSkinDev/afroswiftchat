@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card">
                
                <div class="card-header">
                    <span>Group Subject : <b>{{$group->name}}</b></span>
                </div>

                <div class="card-body mainBody" id="mainBody" style="max-height:450px;overflow-y:auto;">
                    <center><span class="badge badge-pi ll" style="color: brown"  v-if="activeUser">@{{ activeUser }} is typing...</span></center>

                <div id="messages">
 
                </div>


                       
                </div>
            </div>
            <div style="margin-top:20px;">
            <input type="text" name="body" class="form-control" placeholder="Send Message.." id="messageBox" v-model="messageBox" @keydown="sendTypingEvent">

                 <button class="btn btn-success" style="margin-top:10px" v-on:click="
        sendMessage">Send</button>
        <div id="error" style="color: red;display: none">Message cannot be empty</div>

                
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
        newMessage:{},
        typing:'',
        activeUser:false,
        typingTimer:false
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
                                '<div class="message mt-4 "><span class="ml-2"><b>You</b></span> <span style="color: #aaa;margin-left: 40px;"> '+this.messages[i].created_at+'</span><div class="box2"><span>'+this.messages[i].content+'</span></div></div>');
                   
                        }
                   
                        else{
                        

                            $("#messages").append(
                                '<div class="message mt-4"><span class="ml-2">'+this.messages[i].user.name+'</span> <span style="color: #aaa;margin-left: 40px;"> '+this.messages[i].created_at+'</span><div class="box1"><span>'+this.messages[i].content+'</span></div></div>');
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
                $("#users").append('<p id='+user.id+'>'+user.name+'</p>');

                $("#popUp").text(user.name+ " Joined");

                $( "#popUp" ).show(); 

                setTimeout(function() {

                    $( "#popUp" ).hide();

                }, 3000);
            })
            .leaving((user) => {
                $("#"+user.id).remove();
                $("#popUp2").text(user.name+ " Left");

                $( "#popUp2" ).show(); 

                setTimeout(function() {

                    $( "#popUp2" ).hide();

                }, 3000);
                
            })
            .listen('NewMessage', (message)=>{
                 console.log(message)
                $("#messages").append(
                    '<div class="message mt-4"><span class="ml-2">'+message.user.name+'</span> <span style="color: #aaa;margin-left: 40px;"> '+message.created_at+'</span><div class="box1"><span>'+message.content+'</span></div></div>');
                  
                  
                  var myDiv = document.getElementById("mainBody");
                  myDiv.scrollTop = myDiv.scrollHeight;



            })

            .listenForWhisper('typing', (e) => {

                //console.log(e.name)
                this.activeUser=e.name

                if (this.typingTimer) {
                    clearTimeout(this.typingTimer)
                }
                
                this.typingTimer= setTimeout( ()=>{
                    this.activeUser=false
                 },3000)
               
                
            });

        },

        sendMessage(){
            if (this.messageBox=='') {
                 $( "#error" ).show(); 

                setTimeout(function() {

                    $( "#error" ).hide();

                }, 2000);
            }
            else{

            //calling send message endpoint

              axios.post(`/group/${this.group.id}`, {
                 content:this.messageBox 
              })  
              .then( (response)=>{

                 
                    this.newMessage=response.data

                   
                 this.messageBox=''
                 
                 

                 $("#messages").append(
                    '<div class="message mt-3" id="new' +this.newMessage.id+'"><span class="ml-2"><b>You</b></span> <span style="color: #aaa;margin-left: 40px;"> '+this.newMessage.created_at+'</span><div class="box2"><span>'+this.newMessage.content+'</span></div></div>');
                      
                    // scroll to last message
                      var myDiv = document.getElementById("mainBody");
                      myDiv.scrollTop = myDiv.scrollHeight;

               
    
                })

              .catch( function (error){
                  console.log(error);
              })

          }
        //calling endpoint ends                
                           
        },

        sendTypingEvent(){
            Echo.join('chatroom.'+this.group.id)
                .whisper('typing', {
                    name: this.LoggedInUser.name,
             });
               
        },

    }
});



</script>
@endsection
