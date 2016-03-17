@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">記録</div>
                <div class="panel-body">
                    <p>
                        <button class="init" data-xgid="{{ $xgid }}">INIT</button>
                        <button class="turn">黒⇔白</button>
                        <button class="action 00" data-value="00">00</button>
                    </p>
                    <p>
                        <button class="action 11" data-value="11">11</button>
                        <button class="action 12" data-value="12">12</button>
                        <button class="action 13" data-value="13">13</button>
                        <button class="action 14" data-value="14">14</button>
                        <button class="action 15" data-value="15">15</button>
                        <button class="action 16" data-value="16">16</button>
                        <button class="action 66" data-value="66">66</button>
                    </p>
                    <p>
                        <button class="action 22" data-value="22">22</button>
                        <button class="action 23" data-value="23">23</button>
                        <button class="action 24" data-value="24">24</button>
                        <button class="action 25" data-value="25">25</button>
                        <button class="action 26" data-value="26">26</button>
                        <button class="action 55" data-value="55">55</button>
                        <button class="action 56" data-value="56">56</button>
                    </p>
                    <p>
                        <button class="action 33" data-value="33">33</button>
                        <button class="action 34" data-value="34">34</button>
                        <button class="action 35" data-value="35">35</button>
                        <button class="action 36" data-value="36">36</button>
                        <button class="action 44" data-value="44">44</button>
                        <button class="action 45" data-value="45">45</button>
                        <button class="action 46" data-value="46">46</button>
                    </p>
                    <div id="mainRate" class="rate">
                        <div class="win normal"></div>
                        <div class="lose normal"></div>
                        <div class="win gammon"></div>
                        <div class="lose gammon"></div>
                        <div class="win backgammon"></div>
                        <div class="lose backgammon"></div>
                    </div>
                    <img id="gammon" data-xgid="{{ $xgid }}" src="/images/xg/{{ $xgid }}" class="xgid" />
                </div>
                <div class="panel-heading" id="analist">解析</div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('more-style')
<style>
img.xgid {
    width: 100px;
}
.rate {
    position: relative;
    width: 100%;
}
.rate>.win, .rate>.lose {
    position: absolute;
    top: 0;
}
.rate>.win { left: 0; }
.rate>.win.normal { background-color: #9F9; }
.rate>.win.gammon { background-color: #0F0; }
.rate>.win.backgammon { background-color: #0C0; }
.rate>.lose { right: 0; }
.rate>.lose.normal { background-color: #F99; }
.rate>.lose.gammon { background-color: #F00; }
.rate>.lose.backgammon { background-color: #C00; }
.rate, .rate>div {
    height: 10px;
}
.current {
    color: #C33;
}
</style>
@endsection

@section('more-javascript')
<script src="http://underscorejs.org/underscore-min.js"></script>

<script id="move-template" type="text/template">
    <div class="panel-body analist">
        <div><button class="next-turn" data-xgid="<%= nextXgid %>"><%= value %></button>[<%= equity %>(<%= equityDiff %>)]</div>
        <div class="rate">
            <div class="win normal"></div>
            <div class="lose normal"></div>
            <div class="win gammon"></div>
            <div class="lose gammon"></div>
            <div class="win backgammon"></div>
            <div class="lose backgammon"></div>
        </div>
        <div><img src="/images/xg/<%= xgid %>?m=<%= value %>" class="xgid" /></div>
    </div>
</script>

<script id="cube-template" type="text/template">
    <div class="panel-body analist">
        <div><button class="next-turn" data-xgid="<%= xgid %>"><%= value %></button>[<%= equity %>(<%= equityDiff %>)]</div>
        <div><img src="/images/xg/<%= xgid %>" class="xgid" /></div>
    </div>
</script>

<script id="cube-template-null" type="text/template">
    <div class="panel-body analist">
        <div><%= value %>[<%= equity %>(<%= equityDiff %>)]</div>
    </div>
</script>

<script type="text/javascript">
$(function() {
    var moveTemplate = _.template($('#move-template').text());
    var cubeTemplate = _.template($('#cube-template').text());
    var cubeTemplateNull = _.template($('#cube-template-null').text());
    var gammon = $('#gammon');
    var setRate = function(e, rate) {
        $(this).children('.win.normal').css('width', (rate.win * 100) + '%');
        $(this).children('.win.gammon').css('width', (rate.winGammon * 100) + '%');
        $(this).children('.win.backgammon').css('width', (rate.winBackgammon * 100) + '%');
        $(this).children('.lose.normal').css('width', (rate.lose * 100) + '%');
        $(this).children('.lose.gammon').css('width', (rate.loseGammon * 100) + '%');
        $(this).children('.lose.backgammon').css('width', (rate.loseBackgammon * 100) + '%');
    };
    gammon.setXgid = function(xgid) {
        gammon.data('xgid', xgid);
        gammon.attr('src', '/images/xg/'+xgid);
        gammon.reset();
    };
    gammon.setDice = function(v) {
        $.ajax('/record/dice/'+gammon.data('xgid')+'/'+v).done(function(data) {
            gammon.setXgid(data);
        });
    };
    gammon.turn = function() {
        $.ajax('/record/turn/'+gammon.data('xgid')).done(function(data) {
            gammon.setXgid(data);
        });
    };
    gammon.reset = function() {
        $('.panel-body.analist').remove();
        $.ajax('/calc/'+gammon.data('xgid')).done(function(data) {
            console.log(data);
            if (data.result) {
                $.each(data.cubeEquities, function() {
                    var vvv = null;
                    if (this.xgid == null) {
                        vvv = $(cubeTemplateNull({
                            value: this.value,
                            equity: this.equity,
                            equityDiff: this.equityDiff,
                        }));
                    } else {
                        vvv = $(cubeTemplate({
                            value: this.value,
                            equity: this.equity,
                            equityDiff: this.equityDiff,
                            xgid: this.xgid,
                        }));
                    }
                    $('#analist').parent().append(vvv);
                });
                $.each(data.moveEquities, function() {
                    var vvv = $(moveTemplate({
                        value: this.value,
                        equity: this.equity,
                        equityDiff: this.equityDiff,
                        xgid: $('#gammon').data('xgid'),
                        nextXgid: this.xgid,
                    }));
                    vvv.find('.rate').on('setRate', setRate).trigger('setRate', this.rate);
                    $('#analist').parent().append(vvv);
                });
                $('#mainRate').trigger('setRate', data.rate);
            }
        });
        $.ajax('/record/dice/'+$('#gammon').data('xgid')).done(function(data) {
            $('.action').removeClass('current');
            $('.action.'+data).addClass('current');
        });
    };

    gammon.reset();

    $('.init').on('click', function() {
        gammon.setXgid($(this).data('xgid'));
    });
    $('.action').on('click', function() {
        if (!$(this).hasClass('current')) {
            gammon.setDice($(this).data('value'));
        }
    });
    $('.turn').on('click', gammon.turn);
    $('.rate').on('setRate', setRate);
    $('body').on('click', '.next-turn', function() {
        gammon.setXgid($(this).data('xgid'));
    });
});
</script>
@endsection
