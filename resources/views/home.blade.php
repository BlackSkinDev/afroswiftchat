@extends('layouts.app')


@section('content')


<div class="container">

    <h3 style="color: white">Welcome to SwiftChat....Over {{$users}} users</h3>
    <div class="row justify-content-center mt-5">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header" >{{ __('Chat Rooms Available on SwiftChat') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    <div id="groups">
                        @if( count($groups)<1)
                            <span id='noGroup'>
                            <h4 class="animate__animated animate__bounce">Create a group to get started..</h4></span>

                        @else
                            @foreach($groups as $key => $group)

                              <div id="group{{$group->id}}">
                                <div class="row">

                                    <div class="col-md-6">
                                        <span>{{$group->name}}</span>
                                    </div>

                                    <div class="col-md-6">
                                        <span><a href="{{route('join',$group->id)}}"><button class="badge badge-success">Enter Group</button></a></span>
                                        @can('delete',$group)
                                        <a href="#" style="color: black"  v-on:click="deleteGroup({{$group->id}})"><span class="ml-5"><i class="fa fa-remove">&nbsp<b>Remove</b></i></span></a>
                                        @endcan
                                    </div>

                                </div>
                                <hr>
                              </div>


                            @endforeach

                        @endif


                    </div>
                </div>
            </div>

        </div>
        <div class="col-md-6 col2">
            <div class="card">
                <div class="card-header"> <b>{{Auth::user()->name}}</b>, Create chat room and invite friend via the link</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('post') }}">
                        @csrf
                        @if(Session::has('message'))
                        <center>
                            <div class="alert alert-success" style="width: 300px">
                                {{Session::get('message') }}
                            </div>
                        </center>
                        @endif


                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('Group Subject') }}</label>

                            <div class="col-md-6">
                                <input id="subject" type="subject" class="form-control @error('subject') is-invalid @enderror" name="subject" value="{{ old('subject') }}"  autocomplete="subject" autofocus>

                                @error('subject')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>


                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Create Chat room') }}
                                </button>

                            </div>
                        </div>
                    </form>
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
        groups:{!! $groups->toJson() !!},
        group:{},
    },
    mounted(){

        this.listen()
    },
    methods:{

        listen(){
          Echo.private('new-group')
            .listen('NewGroupCreated', (group)=>{

                $("#noGroup").remove()

               $("#groups").append(
                '<div class="row"><div class="col-md-6"><span>'+ group.name+ '</span></div><div class="col-md-6"><span><a href="/join/'+group.id+'"><button class="badge badge-success">Enter Group</button></a></span></div></div><hr>')

            })
            .listen('DeleteGroup', (group)=>{

                 $("#group"+group.id).remove()

            })
        },

        deleteGroup(x){
            this.group=x
            axios.get(`/deletegroup/${this.group}`)

                .then( (response)=>{

                    $("#group"+x).remove()


                })
                .catch( function (error){
                    console.log(error);
                })

        },

    }
});

</script>
@endsection
