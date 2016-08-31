@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10">
                <div class="panel panel-default">
                    <div class="panel-heading">New net</div>

                    <div class="panel-body">

                        @include('errors.common')

                        <form action="{{ route('net.store') }}" method="POST">
                            {{ csrf_field() }}
                            <div class="row" style="margin-bottom:10px;">
                                <div class="form-group">
                                    <div class="col-md-6">
                                        <input type="text" name="name" id="net-name" class="form-control" placeholder="Name">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group">
                                    <div class="col-md-6 text-left">
                                        <a class="btn btn-default" href="{{ route('net.index') }}">Cancel</a>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <button type="submit" class="btn btn-primary">Create</button>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
