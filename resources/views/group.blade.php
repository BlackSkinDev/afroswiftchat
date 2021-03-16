@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row ">
        <div class="col-md-7">
            <div class="card">

                <div class="card-header">
                    <div>
                        <b>Chat</b>
                        <a href="{{route('home')}}"><i class="fa fa-home fa-2x" style="float: right;color: black"></i></a>
                    </div>
                    <center><span id="IsTyping" class="badge badge-pill" style="color: brown;"  v-if="activeUser">@{{ activeUser }} is typing...</span></center>
                </div>

                <div class="card-body mainBody visible-xs-block" id="mainBody" style="max-height:450px;overflow-y:auto;background:url('/img/bg.png');background-size: cover">

                <div id="messages">

                </div>



                </div>
            </div>
            <div style="margin-top:20px;">
            <textarea  onkeyup="this.style.height='auto';this.style.height=(this.scrollHeight)+'px'" class="form-control" placeholder="Send Message.." id="messageBox" v-model="messageBox" @keydown="sendTypingEvent"></textarea>

                <div class="d-flex">
                    <span>
                        <i class="fa fa-send fa-2x" style="margin-top:10px;color: white" v-on:click="
                        sendMessage"></i>
                        <div id="error" style="color: red;display: none">Message cannot be empty</div>       
                    </span>
                    <span class="ml-3 mt-2" style="color:white">
                        <form enctype="multipart/form-data" method="post">   @csrf                     
                          <label for="file">
                          <input type="file" id="file" style="display: none" name="image" accept="image/gif,image/jpeg,image/jpg,image/png" multiple="" data-original-title="upload photos">
                          <i class="fa fa-camera fa-2x mt-1" id="clip"></i>
                        </label>
                        </form>
                        

                    </span>
                </div>
                 
             
            </div>

        </div>

        <div class="col-md-3 col2">
            <div class="card">
                <div class="card-header"><b>Current members</b></div>

                <div class="card-body">
                    <div class="alert alert-success" id="popUp" style="display: none;"></div>
                    <div class="alert alert-danger" id="popUp2" style="display: none;"></div>
                    <div class="alert alert-secondary" id="popUp3" style="display: none;"></div>
                    <div id="users" >

                    </div>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="link" value="{{url()->current()}}" aria-describedby="basic-addon2" disabled>
                        <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" onclick="copy('link')" title="Copy Video Link">Link</button>

                        </div>
                </div>
            </div>


        </div>


    </div>

    <div class="col-md-2 col2">
        <div class="card" style="width: 18rem;">
        <div class="card-header">
           <b> Group Info</b>
        </div>
          <ul class="list-group list-group-flush">
             <li class="list-group-item"><b title="Total messages sent to this group">Group Creator</b>:<span> {{$group->user->name}}</span></li>
           
            <li class="list-group-item">Date created: {{ date("jS F Y",strtotime($group->created_at))}}</li>
            <li class="list-group-item"><b title="Total messages sent to this group">Total Messages</b>:<span id="totalmsg"> {{$group->messages->count()}}</span></li>
            <li class="list-group-item"><b title="Messages you have sent to this group">Messages sent</b>: <span id="mymsg">{{Auth::user()->TotalMessageSent($group->id)}}</li>

          </ul>
        </div>

    </div>


</div>




@endsection

@section('scripts')
<script src="{{ asset('js/app.js') }}" ></script>
<script>
 $(document).ready(function(){
    
     $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#file").change(function(){
      var formData = new FormData();
      var file = document.getElementById("file").files[0];
      formData.append("file", file);
      
        $.ajax({
            type: "POST",
            url: '/send/{{$group->id}}',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
               data=JSON.parse(response)
               
               
               $("#messages").append(
                    '<div class="message mt-3 me" id="new' +data.id+'"><span style="color: #F0FFF0;font-size:12px" class="ml-3"> '+data.created_at+'</span><div class="box3"> <img src="'+data.image+'" class="msg mt-2"></div></div>');

                var mymsgspan= $("#mymsg").text()
                    newmsgtotal= Number(mymsgspan)+1
                    $("#mymsg").text(newmsgtotal)


                    var mymsg= $("#totalmsg").text()
                    totalmsg= Number(mymsg)+1
                    $("#totalmsg").text(totalmsg)


                    // scroll to last message
                      var myDiv = document.getElementById("mainBody");
                      myDiv.scrollTop = myDiv.scrollHeight;

               
            },
            error: function (error) {
                
            }
        });

  });  

 })
  

    function linkify(text) {
        var exp = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
        return text.replace(exp, "<a href='$1' target='_blank'>$1</a>");
    }
     function copy(element_id){
              var aux = document.createElement("div");
              aux.setAttribute("contentEditable", true);
              aux.innerHTML = document.getElementById(element_id).value;
              aux.setAttribute("onfocus", "document.execCommand('selectAll',false,null)");
              document.body.appendChild(aux);
              aux.focus();
              document.execCommand("copy");
              document.body.removeChild(aux);
               
               $("#popUp3").text("Link Copied");

                $( "#popUp3" ).show();

                setTimeout(function() {

                    $( "#popUp3" ).hide();

                }, 3000);
            }


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
        typingTimer:false,
        token:document.head.querySelector('meta[name="csrf-token"]').content
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

                
                   for (let i = 0; i < Object.keys(this.messages).length; i++) {

                        const messageDate= Object.keys(this.messages)[i];

                        const dateMessages=this.messages[messageDate];
                        const colors= ['purple','tomato','yellow','blue','pink']
                        $("#messages").append('<center><span style="font-size:12px;color:white"><b>'+ messageDate +'</b></span></span')

                        for (let j=0; j< dateMessages.length;j++){
                                
                            if (this.LoggedInUser.id == dateMessages[j].user.id) {
                                if (dateMessages[j].content==null) {

                                      $("#messages").append(
                                    '<div class="message mt-3 me"><span class="ml-2"></span> <span style="color: #F0FFF0;font-size:12px"> '+dateMessages[j].created_at+'</span><div class="box3"> <img src="'+dateMessages[j].image+'" class="msg"></div></div>');

                                }
                                else{
                                   $("#messages").append(
                                '<div class="message mt-3 me "><span class="ml-2"></span> <span style="color: #F0FFF0;font-size:12px"> '+dateMessages[j].created_at+'</span><div class="box2"><span style="font-size:12px">'+linkify(dateMessages[j].content)+'</span></div></div>');
                                }
                                
                            }
                            else{
                                var color = colors[Math.floor(Math.random() * colors.length)];

                                if (dateMessages[j].content==null) {
                                        $("#messages").append(
                                    '<div class="message mt-3"><span style="color:'+color+'">'+dateMessages[j].user.name+'</span><span style="color: #F0FFF0;font-size:12px" class="ml-3"> '+dateMessages[j].created_at+'</span><div class="box31"> <img src="'+dateMessages[j].image+'" class="msg mt-2"></div></div>');

                                }
                                else{
                                    $("#messages").append(
                                '<div class="message mt-3"><span style="color:'+color+'" class="ml-2">'+dateMessages[j].user.name+'</span> <span style="color: #F0FFF0;font-size:12px;margin-left:25px"> '+dateMessages[j].created_at+'</span><div class="box1"><span style="font-size:12px">'+linkify(dateMessages[j].content)+'</span></div></div>');
                                
                                }

                                
                            }
                            $("#messages").append("<div class='mt-2'></div>")
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
                        $("#users").append('<p id='+user[i].id+'><i class="fa fa-user fa-1x"><b class="ml-2">'+user[i].name+'</b></p>');
                    }
                    else{
                        $("#users").append('<p id='+user[i].id+'><i class="fa fa-user fa-1x"><b class="ml-2" >'+user[i].name+'</b></p>');
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
                const colors= ['purple','tomato','yellow','blue','pink']
                  var color = colors[Math.floor(Math.random() * colors.length)];

                   if (message.content==null) {
                      $("#messages").append(
                    '<div class="message mt-4"><span style="color:'+color+'">'+message.user.name+'</span> <span style="color: #F0FFF0;margin-left: 40px;"font-size:12px;class="ml-3"> '+message.created_at+'</span><div class="box31"><img src="'+message.image+'" class="msg mt-2"></div></div>');

                   }
                   else{
                      $("#messages").append(
                    '<div class="message mt-4"><span style="color:'+color+'">'+message.user.name+'</span> <span style="color: #F0FFF0;margin-left: 40px;"font-size:12px;class="ml-3"> '+message.created_at+'</span><div class="box1"><span style="font-size:12px" class="mt-2">'+linkify(message.content)+'</span></div></div>');
  
                   }
                  
                    var mymsgspan= $("#totalmsg").text()
                    newmsgtotal= Number(mymsgspan)+1
                    $("#totalmsg").text(newmsgtotal)

                  var myDiv = document.getElementById("mainBody");
                  myDiv.scrollTop = myDiv.scrollHeight;



            })

            .listenForWhisper('typing', (e) => {

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

              axios.post(`/send/${this.group.id}`, {
                 content:this.messageBox
              })
              .then( (response)=>{


                    this.newMessage=response.data


                 this.messageBox=''


                 $("#messages").append(
                    '<div class="message mt-3 me" id="new' +this.newMessage.id+'"><span style="color: #F0FFF0;font-size:12px" class="ml-3"> '+this.newMessage.created_at+'</span><div class="box2"><span style= "font-size:12px" class="mt-2">'+linkify(this.newMessage.content)+'</span></div></div>');
                  
                    var mymsgspan= $("#mymsg").text()
                    newmsgtotal= Number(mymsgspan)+1
                    $("#mymsg").text(newmsgtotal)


                    var mymsg= $("#totalmsg").text()
                    totalmsg= Number(mymsg)+1
                    $("#totalmsg").text(totalmsg)


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

