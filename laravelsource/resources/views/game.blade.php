@extends('layouts.game')

@section('title', 'The Last Stand: Dead Zone')

<script type="text/javascript">
    window.gameToken = "{{ $token ?? '' }}";
    window.API_BASE_URL = "{{ config('app.api_base_url', env('API_BASE_URL', 'http://127.0.0.1:8080')) }}";
</script>

@section('content')
<div id="wrapper">
    <a name="top"></a>
    <div id="header">
        <a id="logo" href="#" onclick="refresh()"><img src="{{ config('app.url') }}/assets/images/logo.png" alt="TLS" /></a>
        <div id="nav">
            <ul id="nav-ul" class="play">
                <li id="get-more"><a href="#top" onclick="openGetMoreDialogue()">Get More</a></li>
                <li id="code"><a href="#top" onclick="openRedeemCodeDialogue()">Redeem Code</a></li>
                <li id="fan-page"><a href="https://deadzonegame.net" target="_blank" onclick="updateNavClass('fan-page')">Fan Page</a></li>
                <li id="help"><a href="https://discord.gg/7EyxwYEush" onclick="updateNavClass('help')" target="_blank">Help</a></li>
                <li id="forum"><a href="https://discord.gg/7EyxwYEush" onclick="updateNavClass('forum')">Forum</a></li>
            </ul>
        </div>
        <div id="fb-likes">
            <iframe
                src="https://www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2FLastStandDeadZone&send=false&layout=button_count&width=200&show_faces=true&action=like&colorscheme=dark&font&height=21"
                scrolling="no" frameborder="0" style="max-width:85px;" allowtransparency="true" data-ruffle-polyfilled=""></iframe>
        </div>
        <div class="debug-container">
            <p class="server-status"><a href="https://status.deadzonegame.net" target="_blank">Server Status: N/A</a></p>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-btn bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600 py-2 px-4 rounded-lg font-medium text-sm text-white transition-all">Logout</button>
            </form>
        </div>
    </div>

    <div id="warning-container"></div>
    <div id="message-container"></div>

    @if (session('status'))
        <div class="flash-message flash-success">
            {{ session('status') }}
        </div>
    @endif
    @if (session('error'))
        <div class="flash-message flash-error">
            {{ session('error') }}
        </div>
    @endif
    @if (session('message'))
        <div class="flash-message flash-info">
            {{ session('message') }}
        </div>
    @endif

    <div id="content">
        <div id="game-wrapper">
            <div id="game-container">
                <div id="noflash" class="error">
                    <h2>Flash Player Required</h2>
                    <p><strong>The Last Stand: Dead Zone</strong> requires the latest version of Adobe<sup>®</sup> Flash<sup>®</sup> Player.<br />
                    It's free, and only takes a small amount of time to download.</p>
                    <p>Required version: <strong><span id="noflash-reqVersion"></span></strong></p>
                    <p>Currently running version: <strong><span id="noflash-currentVersion"></span></strong></p>
                    <div id="download-flash">
                        <p><a href="flashplayer" title="Download Flash Player"><strong>Download Flash Player</strong></a></p>
                        <p><a href="?detectflash=false" title="I already have the latest Flash Player">I already have the latest Flash Player!</a></p>
                    </div>
                </div>
            </div>
        </div>
        <div id="generic-error" class="error"></div>
    </div>

    <div id="footer">
        <a href="https://switchcompagnie.eu/terms" target="_blank">Terms</a> |
        <a href="https://switchcompagnie.eu/terms" target="_blank">Privacy</a> |
        © 2025 Ruby Realms Studio - V0.2
    </div>

    <div id="user-id">
        User ID : {{ Auth::check() ? Auth::user()->id : 'Connecting...' }}
    </div>
    <div class="ruby-realms-logo">
        <a href="https://switchcompagnie.eu" title="Ruby Realms Studio" target="_blank">
            <img src="{{ config('app.url') }}/assets/images/suliartist.png" alt="Ruby Realms Studio">
        </a>
    </div>
</div>
@endsection
