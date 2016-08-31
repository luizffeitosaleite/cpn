@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10">
                <div class="panel panel-default">
                    <div class="panel-heading">Petri nets</div>

                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <a href="{{ route('net.create') }}" class="btn btn-primary"><i class="glyphicon glyphicon-plus"></i></a>
                            </div>
                        </div>

                        <div class="row">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>
                                                Name
                                            </th>
                                            <th>
                                                Author
                                            </th>
                                            <th>
                                                Action
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(count($nets)>0)
                                            @foreach($nets as $net)
                                                <tr>
                                                    <td>{{ $net->name }}</td>
                                                    <td>{{ $net->author->name }}</td>
                                                    <td><a class="btn btn-primary" href="{{ route('net.edit', ['id'=>$net->id]) }}"><i class="glyphicon glyphicon-edit"></i></a></td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="3" class="text-center">No nets created</td>
                                            </tr>
                                        @endif

                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
