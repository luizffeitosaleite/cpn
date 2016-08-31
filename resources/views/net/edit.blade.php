@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ URL::asset('css/joint.min.css') }}">
@endsection


@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10">
                <div class="panel panel-default">
                    <div class="panel-heading">{{ $net->name }}</div>

                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <button onclick="addPlace()" class="btn btn-default">Place</button>
                                <button onclick="addTransition()" class="btn btn-default">Transition</button>
                                <button onclick="fireRandTransition()" class="btn btn-default">Step</button>
                                <button onclick="playSimulation()" class="btn btn-default">Play/Pause</button>
                                <input style="display:inline;width:64px;" class="form-control" min="1" type="number" name="fast-forward" id="fast-forward">
                                <button onclick="fastForward()" class="btn btn-default">Fast forward</button>
                                <button onclick="saveNet()" class="btn btn-default">Save</button>
                                <button onclick="loadNet()" class="btn btn-default">Load</button>
                            </div>
                        </div>
                        <div class="row">
                            <div id="paper" class="col-md-offset-1 col-md-10" style="margin-top:10px;border-radius:4px;border:1px solid #ddd;height:450px;overflow:scroll;background-color:#f2f2f2;"></div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Properties</div>
                    <div id="properties" class="panel-body">
                        No element selected
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript" src="{{ URL::asset('js/lodash.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/backbone-min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/joint.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/joint.shapes.pn.min.js') }}"></script>

    <script type="text/javascript">
        var netStruct = 'net';

        var play = false;
        var simId =  0;

        var graph = new joint.dia.Graph();
        var paper = new joint.dia.Paper({
           el: $("#paper"),
            width: 800,
            height: 600,
            gridSize: 5,
            perpendicularLinks: true,
            model: graph
        });


        var backupPosition = 'position';
        var selectedId = 'id';


        var pn = joint.shapes.pn;

        var addPlace = function(){
            var place = new pn.Place({
                position: {x: 25, y:25},
                attrs: { '.label': {text: 'place'}}, tokens: 0
            });

            graph.addCell([place]);
        };

        var addTransition = function(){
            var transition = new pn.Transition({
                position: {x: 25, y:25},
                attrs: { '.label': {text: 'transition'}}
            });

            graph.addCell([transition]);
        };

        var link = function (output, input, origin){
            if(origin == 'pn.Transition')
            {
                var type = 'tp';
            } else
            {
                var type = 'pt';
            }
            return new pn.Link({
                source: {id: output.id, selector:'.root'},
                target: {id: input.id, selector:'.root'},
                attrs: {
                    '.connection':{
                        'fill': 'none',
                        'stroke-linejoin': 'round',
                        'stroke-width': '2',
                        'stroke': '#4b4a67'
                    },
                    'type': type
                }
            });
        };

        var clearProperties = function(){
            $("#properties").html('No element selected');
        };

        var removeElement = function(eId){
            clearProperties();
            graph.getCell(eId).remove();
        };

        var setTokens = function(pId, tokens){
            tokens = parseInt(tokens);
            graph.getCell(pId).set('tokens', tokens);
        };

        var setLabel = function(eId, label){
            graph.getCell(eId).attr('.label/text', label);
        };

        var setProperties = function(cellView){
            var cell = cellView.model;
            var id = cell.get('id');
            var type = cell.get('type');

            if(type == 'pn.Place'){
                var properties = "<label>Type:</label><br/><input class='form-control disabled' disabled='true' value='Place'><br/>";
                properties += "<label>Label:</label><br/><input class='form-control' onchange='setLabel("+'"'+id+'"'+", this.value)' type='text' value='"+cell.attr('.label/text')+"'><br/>";
                properties += "<label>Tokens:</label><br/><input class='form-control' min='0' onchange='setTokens("+'"'+id+'"'+", this.value)' type='number' value='"+cell.get('tokens')+"'><br/>";
                properties += "<button class='btn btn-danger pull-right' onclick='removeElement("+'"'+id+'"'+")'>Remove</button><br/>";

                $("#properties").html(properties);
            }

            if(type == 'pn.Transition'){
                var properties = "<label>Type:</label><br/><input class='form-control disabled' disabled='true' value='Transition'><br/>";
                properties += "<label>Label:</label><br/><input class='form-control' onchange='setLabel("+'"'+id+'"'+", this.value)' type='text' value='"+cell.attr('.label/text')+"'><br/>";
                properties += "<button class='btn btn-primary' onclick='fireTransition("+'"'+id+'"'+")'>Fire</button>";
                properties += "<button class='btn btn-danger pull-right' onclick='removeElement("+'"'+id+'"'+")'>Remove</button><br/>";

                $("#properties").html(properties);
            }

        }

        paper.on('blank:pointerclick', function(){
            clearProperties();
        });

        paper.on('cell:pointerclick', function(cellView, evt, x, y){
            setProperties(cellView, x, y);
        });

        paper.on('cell:pointerdown', function(cellView, evt, x, y){
            backupPosition = cellView.model.get('position');
            selectedId = cellView.model.id;
        })

        paper.on('cell:pointerup', function(cellView, ect, x, y){

            var elementBelow = graph.get('cells').find(function(cell){
                if(cell instanceof joint.dia.Link){
                    return false;
                }

                if(cell.id === cellView.model.id){
                    return false;
                }

                return !!(cell.getBBox().containsPoint(g.point(x, y)) && cell.get('type') !== cellView.model.get('type'));
            });

            if(elementBelow){
                graph.addCell([link(cellView.model, elementBelow, cellView.model.get('type'))]);
                if(selectedId == cellView.model.id){
                    cellView.model.set('position', backupPosition);
                }
            }
        });

        var canFire = function(tId){
            var canFire = true;

            var transition = graph.getCell(tId);
            var places = new Array();
            var inbound = graph.getConnectedLinks(transition, {inbound: true});

            inbound.forEach(function(e){
                var consult = $.grep(places, function(p){return p.pId == e.get('source').id;});
                if(consult.length == 0){
                    places.push({'pId': e.get('source').id, 'qty': 1});
                } else {
                    consult[0].qty = consult[0].qty + 1;
                }
            });

            places.forEach(function(p){
                var place = graph.getCell(p.pId);
                if(place.get('tokens') < p.qty){
                    canFire = false;
                }
            });

            return canFire;
        }

        var fireTransition = function(tId){


            if(canFire(tId)){
                var sec = 0.8;//time to token animation;

                var transition = graph.getCell(tId);

                var inbound = graph.getConnectedLinks(transition, {inbound: true});

                var outbound = graph.getConnectedLinks(transition, {outbound: true});

                var placesBefore = _.map(inbound, function(link) {
                    return graph.getCell(link.get('source').id);
                });

                var placesAfter = _.map(outbound, function(link){
                    return graph.getCell(link.get('target').id);
                });

                _.each(placesBefore, function(p){
                    _.defer(function() {p.set('tokens', p.get('tokens') - 1); });

                    var link = $.grep(inbound, function(l){return l.get('source').id === p.id});

                    link.forEach( function (l){
                       paper.findViewByModel(l).sendToken(V('circle', { r: 5, fill: '#feb662' }).node, sec * 1000);
                    });
                    /*var link = _.find(inbound, function(l) { return l.get('source').id === p.id; });
                    paper.findViewByModel(link).sendToken(V('circle', { r: 5, fill: '#feb662' }).node, sec * 1000);*/
                });

                _.each(placesAfter, function(p){
                    var link = _.find(outbound, function(l) { return l.get('target').id === p.id; });
                    paper.findViewByModel(link).sendToken(V('circle', { r: 5, fill: '#feb662' }).node, sec * 1000, function() {
                        p.set('tokens', p.get('tokens') + 1);
                    });
                });
            }

        };

        var fireRandTransition = function ()
        {
            var transitions = $.grep(graph.getElements(), function(e){return e.get('type')== 'pn.Transition'});
            var toFire = $.grep(transitions, function(t){return canFire(t.get('id'))});

            var t = toFire[Math.floor(Math.random()*(toFire.length))];
            fireTransition(t.get('id'));
        };


        var playSimulation = function ()
        {
            if(!play){
                play = true;
                simId = simulate();
            } else {
                play = false;
                clearInterval(simId);
            }
        };

        var simulate = function() {
            return setInterval(function(){fireRandTransition();}, 1000);

        };
        
        var fastForward = function () {
            var dataT = new Array();
            var dataP = new Array();

            var transitions = $.grep(graph.getElements(), function(e){return e.get('type')== 'pn.Transition'});

            var places = $.grep(graph.getElements(), function(e){return e.get('type')== 'pn.Place'});

            transitions.forEach(function(t){
                var ps = new Array();
                var pt = new Array();
                var inbound = graph.getConnectedLinks(t, {inbound: true});

                inbound.forEach(function(e){
                    var consult = $.grep(ps, function(p){return p.pId == e.get('source').id;});
                    if(consult.length == 0){
                        ps.push({'pId': e.get('source').id, 'qty': 1});
                    } else {
                        consult[0].qty = consult[0].qty + 1;
                    }
                });

                var outbound = graph.getConnectedLinks(t, {outbound: true});

                outbound.forEach(function(e){
                    var consult = $.grep(pt, function(p){return p.pId == e.get('target').id;});
                    if(consult.length == 0){
                        pt.push({'pId': e.get('target').id, 'qty': 1});
                    } else {
                        consult[0].qty = consult[0].qty + 1;
                    }
                });

                dataT.push({'tId': t.get('id'), 'ps': ps, 'pt': pt});

            });

            places.forEach(function(p){
               dataP.push({'pId': p.get('id'), 'tokens': p.get('tokens')});
            });


            var url = "{{ route('net.simulate') }}";

            $.ajax({
                'type': 'POST',
                'url': url,
                'data': {'steps': $('#fast-forward').val(),'transitions': dataT, 'places': dataP},
                'dataType': 'json',
                'success': function (response){
                    response.forEach(function(p){
                        setTokens(p.pId, p.tokens);
                    });
                }
            });
        };

        var saveNet = function(){
            netStruct = JSON.stringify(graph);
            var url = "{{ route('state.store', ["net_id"=>$net->id]) }}";

            $.ajax({
                'type': 'POST',
                'url': url,
                'data': {'net_id': "{{ $net->id }}", 'data': netStruct},
                'dataType': 'json',
                'success': function (response){
                    console.log(response);
                },
                'error': function (response) {
                    console.log(response);
                }
            });

        };

        var loadNet = function(){

            var url = "{{ route('state.load', ['net_id'=>$net->id]) }}";

            $.ajax({
                'type': 'GET',
                'url': url,
                'dataType': 'json',
                'success': function (response){
                    netStruct = response;
                    graph.fromJSON(JSON.parse(netStruct));
                }
            });
        };

        $( document ).ready(loadNet());

    </script>
@endsection