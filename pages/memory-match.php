<?php
session_start();
include "../config/db.php";
requireLogin();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Memory Match</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:Arial,sans-serif;background:#f5f7fb;}
.layout{display:flex;min-height:100vh;}
.sidebar{width:250px;background:#fff;padding:20px;border-right:1px solid #e7eaf0;}
.main{flex:1;padding:30px;}
h1{font-size:28px;margin-bottom:5px;}
.sub{color:#64748b;margin-bottom:20px;}
.stats{display:flex;gap:20px;margin-bottom:25px;}
.stat{background:#fff;padding:18px 24px;border-radius:14px;box-shadow:0 4px 15px rgba(0,0,0,0.05);flex:1;}
.stat span{color:#94a3b8;font-size:13px;}
.stat strong{font-size:24px;display:block;}
.board{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;max-width:400px;}
.card{background:#6d5dfc;color:white;border-radius:14px;padding:25px;text-align:center;font-size:28px;cursor:pointer;transition:0.3s;box-shadow:0 4px 15px rgba(109,93,252,0.2);}
.card:hover{transform:translateY(-3px);}
.card.flipped{background:#fff;color:#333;border:2px solid #6d5dfc;}
.card.matched{background:#10b981;color:white;}
.btn{padding:16px 40px;background:linear-gradient(135deg,#6d5dfc,#8b5cf6);color:white;border:none;border-radius:12px;font-size:18px;font-weight:700;cursor:pointer;margin:10px;}
.btn:hover{box-shadow:0 8px 25px rgba(109,93,252,0.3);}
.start-box{text-align:center;padding:60px;background:#fff;border-radius:20px;box-shadow:0 8px 25px rgba(0,0,0,0.05);margin-bottom:25px;}
.hidden{display:none;}
.result{text-align:center;padding:40px;background:#fff;border-radius:20px;box-shadow:0 8px 25px rgba(0,0,0,0.05);margin-top:20px;}
</style>
</head>
<body>
<div class="layout">
<div class="sidebar"><h2>SmritiMitra</h2><p style="color:#94a3b8;margin-top:5px;">AI Cognitive Care</p><hr style="margin:20px 0;"><a href="/SmritiMitra/pages/games.php" style="color:#6d5dfc;text-decoration:none;">Back to Games</a></div>
<div class="main">
<h1>Memory Match</h1>
<p class="sub">Find matching pairs to win!</p>

<div id="startScreen" class="start-box">
<h2 style="margin-bottom:15px;">Ready to Play?</h2>
<p style="color:#64748b;margin-bottom:25px;">Click Start to begin. Sounds and voice will play!</p>
<button class="btn" id="startBtn">Start Game</button>
</div>

<div class="stats hidden" id="statsBar">
<div class="stat"><span>Moves</span><strong id="moves">0</strong></div>
<div class="stat"><span>Matches</span><strong id="matches">0 / 4</strong></div>
</div>

<div id="boardArea" class="hidden" style="margin-bottom:20px;">
<div class="board" id="board"></div>
</div>

<button class="btn hidden" id="restartBtn">Restart</button>

<div id="resultBox" class="result hidden">
<h2 id="resultTitle">You Won!</h2>
<p id="resultText" style="color:#64748b;margin:10px 0 20px;"></p>
<button class="btn" id="againBtn">Play Again</button>
</div>
</div>
</div>

<script>
var audioCtx=null, bgInterval=null, isMuted=localStorage.getItem("gameSoundMuted")==="true";

function unlock(){
    if(audioCtx)return;
    try{audioCtx=new(window.AudioContext||window.webkitAudioContext)();if(audioCtx.state==="suspended")audioCtx.resume();}catch(e){}
}

function tone(f,d,t,v){
    if(isMuted||!audioCtx)return;
    try{var o=audioCtx.createOscillator(),g=audioCtx.createGain();o.type=t||"sine";o.frequency.setValueAtTime(f,audioCtx.currentTime);g.gain.setValueAtTime(v||0.15,audioCtx.currentTime);g.gain.exponentialRampToValueAtTime(0.001,audioCtx.currentTime+d);o.connect(g);g.connect(audioCtx.destination);o.start(audioCtx.currentTime);o.stop(audioCtx.currentTime+d);}catch(e){}
}

function speak(t){if(isMuted||!("speechSynthesis"in window))return;window.speechSynthesis.cancel();var u=new SpeechSynthesisUtterance(t);u.lang="en-IN";u.rate=0.9;window.speechSynthesis.speak(u);}

function matchSfx(){tone(523,0.12,"sine",0.2);setTimeout(function(){tone(659,0.12,"sine",0.2)},120);setTimeout(function(){tone(784,0.18,"sine",0.2)},240);}
function wrongSfx(){tone(200,0.25,"sawtooth",0.12);}
function flipSfx(){tone(440,0.07,"sine",0.12);}
function winSfx(){[523,659,784,1047,784,1047,1319].forEach(function(n,i){setTimeout(function(){tone(n,0.18,"sine",0.18)},i*120)});}
function stopBG(){if(bgInterval){clearInterval(bgInterval);bgInterval=null;}}
function startBG(){
    if(isMuted)return;
    var n=[262,294,330,349,392,349,330,294],idx=0;
    function p(){if(isMuted||!audioCtx)return;try{var o=audioCtx.createOscillator(),g=audioCtx.createGain();o.type="sine";o.frequency.setValueAtTime(n[idx%n.length],audioCtx.currentTime);g.gain.setValueAtTime(0.05,audioCtx.currentTime);g.gain.exponentialRampToValueAtTime(0.001,audioCtx.currentTime+0.45);o.connect(g);g.connect(audioCtx.destination);o.start(audioCtx.currentTime);o.stop(audioCtx.currentTime+0.45);idx++;}catch(e){}}
    p();bgInterval=setInterval(p,500);
}

var E={"A":"\uD83C\uDF4E","B":"\uD83D\uDC36","C":"\uD83C\uDF38","D":"\uD83D\uDE97"};
var cards=[],fCard=null,sCard=null,moves=0,matches=0,locked=false,attempts=0;

function startGame(){
    unlock();stopBG();
    cards=["A","A","B","B","C","C","D","D"].sort(function(){return Math.random()-0.5});
    fCard=null;sCard=null;moves=0;matches=0;locked=false;attempts=0;
    document.getElementById("moves").textContent="0";
    document.getElementById("matches").textContent="0 / 4";
    document.getElementById("startScreen").classList.add("hidden");
    document.getElementById("statsBar").classList.remove("hidden");
    document.getElementById("boardArea").classList.remove("hidden");
    document.getElementById("restartBtn").classList.remove("hidden");
    document.getElementById("resultBox").classList.add("hidden");
    var b=document.getElementById("board");b.innerHTML="";
    cards.forEach(function(v){
        var c=document.createElement("div");c.className="card";c.textContent="?";
        c.setAttribute("data-v",v);
        c.onclick=function(){flip(c);};
        b.appendChild(c);
    });
    speak("Lets start the game! Good luck!");
    setTimeout(startBG,1500);
}

function flip(c){
    if(locked||c.classList.contains("flipped")||c.classList.contains("matched"))return;
    unlock();flipSfx();
    c.classList.add("flipped");c.textContent=E[c.getAttribute("data-v")]||c.getAttribute("data-v");
    if(!fCard){fCard=c;}
    else{sCard=c;moves++;attempts++;document.getElementById("moves").textContent=moves;check();}
}

function check(){
    locked=true;
    if(fCard.getAttribute("data-v")===sCard.getAttribute("data-v")){
        matchSfx();fCard.classList.add("matched");sCard.classList.add("matched");matches++;
        document.getElementById("matches").textContent=matches+" / 4";
        fCard=null;sCard=null;locked=false;
        if(matches===4){
            stopBG();
            setTimeout(function(){winSfx();speak("Hurray! You won!");
            document.getElementById("resultBox").classList.remove("hidden");
            document.getElementById("resultTitle").textContent="You Won!";
            document.getElementById("resultText").textContent="Completed in "+moves+" moves!";
            },500);
        }
    }else{
        wrongSfx();
        setTimeout(function(){fCard.classList.remove("flipped");sCard.classList.remove("flipped");fCard.textContent="?";sCard.textContent="?";fCard=null;sCard=null;locked=false;},800);
    }
}

window.onload=function(){
    document.getElementById("startBtn").onclick=function(){startGame();};
    document.getElementById("restartBtn").onclick=function(){startGame();};
    document.getElementById("againBtn").onclick=function(){startGame();};
};
</script>
</body>
</html>
