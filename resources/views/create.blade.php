@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Welcome')  }}  {{Auth::user()->email}}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('post') }}">
                        @csrf
                        @if(Session::has('message'))
                        <center>
                            <div class="alert alert-success" style="width: 400px">
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
