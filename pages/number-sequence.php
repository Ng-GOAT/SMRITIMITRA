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
<title>Number Sequence</title>
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
.display{background:#fff;border-radius:20px;padding:40px;text-align:center;font-size:36px;font-weight:700;box-shadow:0 4px 15px rgba(0,0,0,0.05);margin-bottom:25px;min-height:80px;display:flex;align-items:center;justify-content:center;}
.nums{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;max-width:350px;margin-bottom:25px;}
.num{background:#6d5dfc;color:white;border:none;border-radius:14px;padding:20px;font-size:24px;font-weight:700;cursor:pointer;}
.num:hover{background:#5b4cdb;}
.btn{padding:16px 40px;background:linear-gradient(135deg,#6d5dfc,#8b5cf6);color:white;border:none;border-radius:12px;font-size:18px;font-weight:700;cursor:pointer;margin:10px;}
.start-box{text-align:center;padding:60px;background:#fff;border-radius:20px;box-shadow:0 8px 25px rgba(0,0,0,0.05);margin-bottom:25px;}
.hidden{display:none;}
.instruction{color:#64748b;font-size:16px;margin-bottom:20px;}
</style>
</head>
<body>
<div class="layout">
<div class="sidebar"><h2>SmritiMitra</h2><p style="color:#94a3b8;margin-top:5px;">AI Cognitive Care</p><hr style="margin:20px 0;"><a href="/SmritiMitra/pages/games.php" style="color:#6d5dfc;text-decoration:none;">Back to Games</a></div>
<div class="main">
<h1>Number Sequence</h1>
<p class="sub">Remember the numbers and repeat the sequence!</p>

<div id="startScreen" class="start-box">
<h2 style="margin-bottom:15px;">Ready to Play?</h2>
<p style="color:#64748b;margin-bottom:25px;">Click Start to test your number memory!</p>
<button class="btn" id="startBtn">Start Game</button>
</div>

<div class="stats hidden" id="statsBar">
<div class="stat"><span>Level</span><strong id="level">1</strong></div>
<div class="stat"><span>Score</span><strong id="score">0</strong></div>
</div>

<p class="instruction hidden" id="instruction">Press Start to begin.</p>
<div class="display hidden" id="display">?</div>
<div class="nums hidden" id="numBtns">
<button class="num" data-n="1">1</button>
<button class="num" data-n="2">2</button>
<button class="num" data-n="3">3</button>
<button class="num" data-n="4">4</button>
<button class="num" data-n="5">5</button>
<button class="num" data-n="6">6</button>
<button class="num" data-n="7">7</button>
<button class="num" data-n="8">8</button>
<button class="num" data-n="9">9</button>
</div>
<div class="hidden" id="answerArea" style="text-align:center;margin-bottom:20px;">
<p style="color:#64748b;">Your answer:</p>
<div class="display" id="answer" style="min-height:50px;font-size:24px;">-</div>
</div>
<button class="btn hidden" id="clearBtn" onclick="clearAns()">Clear</button>
<button class="btn hidden" id="restartBtn">Restart</button>
</div>
</div>

<script>
var audioCtx=null,bgInterval=null,isMuted=localStorage.getItem("gameSoundMuted")==="true";
var seq=[],ans=[],lv=1,sc=0,active=false,totalC=0,totalA=0;

function unlock(){if(audioCtx)return;try{audioCtx=new(window.AudioContext||window.webkitAudioContext)();if(audioCtx.state==="suspended")audioCtx.resume();}catch(e){}}
function tone(f,d,t,v){if(isMuted||!audioCtx)return;try{var o=audioCtx.createOscillator(),g=audioCtx.createGain();o.type=t||"sine";o.frequency.setValueAtTime(f,audioCtx.currentTime);g.gain.setValueAtTime(v||0.15,audioCtx.currentTime);g.gain.exponentialRampToValueAtTime(0.001,audioCtx.currentTime+d);o.connect(g);g.connect(audioCtx.destination);o.start(audioCtx.currentTime);o.stop(audioCtx.currentTime+d);}catch(e){}}
function speak(t){if(isMuted||!("speechSynthesis"in window))return;window.speechSynthesis.cancel();var u=new SpeechSynthesisUtterance(t);u.lang="en-IN";u.rate=0.9;window.speechSynthesis.speak(u);}
function clickSfx(){tone(800,0.08,"sine",0.12);}
function lvlSfx(){tone(523,0.12,"sine",0.18);setTimeout(function(){tone(659,0.12,"sine",0.18)},130);setTimeout(function(){tone(784,0.12,"sine",0.18)},260);setTimeout(function(){tone(1047,0.25,"sine",0.2)},390);}
function wrongSfx(){tone(200,0.25,"sawtooth",0.12);}
function winSfx(){[523,659,784,1047,784,1047,1319].forEach(function(n,i){setTimeout(function(){tone(n,0.18,"sine",0.18)},i*120)});}
function stopBG(){if(bgInterval){clearInterval(bgInterval);bgInterval=null;}}
function startBG(){if(isMuted)return;var n=[262,294,330,349,392,349,330,294],idx=0;function p(){if(isMuted||!audioCtx)return;try{var o=audioCtx.createOscillator(),g=audioCtx.createGain();o.type="sine";o.frequency.setValueAtTime(n[idx%n.length],audioCtx.currentTime);g.gain.setValueAtTime(0.05,audioCtx.currentTime);g.gain.exponentialRampToValueAtTime(0.001,audioCtx.currentTime+0.45);o.connect(g);g.connect(audioCtx.destination);o.start(audioCtx.currentTime);o.stop(audioCtx.currentTime+0.45);idx++;}catch(e){}}p();bgInterval=setInterval(p,500);}

function startGame(){
    unlock();stopBG();seq=[];ans=[];active=false;lv=1;sc=0;totalC=0;totalA=0;
    document.getElementById("level").textContent="1";document.getElementById("score").textContent="0";
    document.getElementById("startScreen").classList.add("hidden");
    document.getElementById("statsBar").classList.remove("hidden");
    document.getElementById("instruction").classList.remove("hidden");
    document.getElementById("display").classList.remove("hidden");
    document.getElementById("numBtns").classList.remove("hidden");
    document.getElementById("answerArea").classList.remove("hidden");
    document.getElementById("clearBtn").classList.remove("hidden");
    document.getElementById("restartBtn").classList.remove("hidden");
    document.getElementById("answer").textContent="-";
    showSequence();
}

function showSequence(){
    active=false;ans=[];document.getElementById("answer").textContent="-";
    var len=lv+2;seq=[];
    for(var i=0;i<len;i++)seq.push(Math.floor(Math.random()*9)+1);
    document.getElementById("display").textContent=seq.join("  ");
    document.getElementById("instruction").textContent="Remember the numbers...";
    speak("Remember the numbers carefully");
    setTimeout(startBG,1000);
    setTimeout(function(){document.getElementById("display").textContent="?";active=true;document.getElementById("instruction").textContent="Now repeat the sequence!";},3000);
}

function addNum(n){if(!active)return;clickSfx();ans.push(n);document.getElementById("answer").textContent=ans.join("  ");if(ans.length===seq.length)checkAns();}
function clearAns(){if(!active)return;ans=[];document.getElementById("answer").textContent="-";}

function checkAns(){
    active=false;totalA++;stopBG();
    if(JSON.stringify(seq)===JSON.stringify(ans)){
        lvlSfx();sc+=10;totalC++;document.getElementById("score").textContent=sc;
        lv++;document.getElementById("level").textContent=lv;
        document.getElementById("instruction").textContent="Correct!";
        setTimeout(showSequence,1500);
    }else{
        wrongSfx();speak("Try again!");
        document.getElementById("instruction").textContent="Wrong! Try again.";
        setTimeout(showSequence,2500);
    }
}

window.onload=function(){
    document.getElementById("startBtn").onclick=function(){startGame();};
    document.getElementById("restartBtn").onclick=function(){startGame();};
    document.querySelectorAll(".num").forEach(function(b){b.onclick=function(){addNum(parseInt(b.getAttribute("data-n")));};});
};
</script>
</body>
</html>
