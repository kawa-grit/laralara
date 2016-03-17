@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">試合とレクチャー</div>
                <div class="panel-body">
                    <div id="game-movie"></div>
                    <div>
                        <button class="game-point" data-time="5000">みどころ1</button>
                        <button class="game-point" data-time="6700">みどころ2</button>
                        <button class="game-point" data-time="7700" data-lecture="lUZO3LPBW_I">みどころ3</button>
                        <button class="game-point" data-time="8700">みどころ4</button>
                        <button class="game-point" data-time="9700" data-lecture="sYhVFI7L-7A">みどころ5</button>
                        <button class="game-point" data-time="10700" data-lecture="OGVEEJeTmxc">みどころ6</button>
                        <button class="game-point" data-time="11700">みどころ7</button>
                        <button class="game-point" data-time="13000" data-lecture="asNVAwJzSoI">みどころ8</button>
                    </div>
                </div>
                <div class="panel-body">
                    <div id="lecture-movie"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('more-javascript')
<script>

// IFrame Player API の読み込み
var tag = document.createElement('script');
tag.src = "https://www.youtube.com/iframe_api";
var firstScriptTag = document.getElementsByTagName('script')[0];
firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

// YouTubeの埋め込み
var gameMoviePlayer;
var lectureMoviePlayer;
function onYouTubeIframeAPIReady() {
  gameMoviePlayer = new YT.Player(
    'game-movie', // 埋め込む場所の指定
    {
      width: 640, // プレーヤーの幅
      height: 390, // プレーヤーの高さ
      videoId: 'k3THN1MEErk' // YouTubeのID
    }
  );
  var timeoutLoop = function() {
    setTimeout(function() {
      timeoutLoop();
    }, 1000);
  };
  timeoutLoop();

  $('#lecture-movie').hide();
  lectureMoviePlayer = new YT.Player(
    'lecture-movie', // 埋め込む場所の指定
    {
      width: 640, // プレーヤーの幅
      height: 390, // プレーヤーの高さ
    }
  );
}

$(function() {
  $('.game-point').click(function() {
    console.log($(this).data());
    gameMoviePlayer.seekTo($(this).data('time'));
    if ($(this).data('lecture')) {
        gameMoviePlayer.pauseVideo();
        lectureMoviePlayer.loadVideoById($(this).data('lecture'));
        $('#lecture-movie').show();
    } else {
        gameMoviePlayer.playVideo();
        $('#lecture-movie').hide();
    }
  });
});

</script>
@endsection
