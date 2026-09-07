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
<title>Pattern Recognition</title>
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
.display{background:#fff;border-radius:20px;padding:40px;text-align:center;font-size:36px;font-weight:700;box-shadow:0 4px 15px rgba(0,0,0,0.05);margin-bottom:25px;}
.opts{display:flex;gap:15px;justify-content:center;margin-bottom:25px;}
.opt{background:#6d5dfc;color:white;border:none;border-radius:14px;padding:20px 30px;font-size:28px;cursor:pointer;}
.opt:hover{background:#5b4cdb;}
.btn{padding:16px 40px;background:linear-gradient(135deg,#6d5dfc,#8b5cf6);color:white;border:none;border-radius:12px;font-size:18px;font-weight:700;cursor:pointer;margin:10px;}
.start-box{text-align:center;padding:60px;background:#fff;border-radius:20px;box-shadow:0 8px 25px rgba(0,0,0,0.05);margin-bottom:25px;}
.hidden{display:none;}
</style>
</head>
<body>
<div class="layout">
<div class="sidebar"><h2>SmritiMitra</h2><p style="color:#94a3b8;margin-top:5px;">AI Cognitive Care</p><hr style="margin:20px 0;"><a href="/SmritiMitra/pages/games.php" style="color:#6d5dfc;text-decoration:none;">Back to Games</a></div>
<div class="main">
<h1>Pattern Recognition</h1>
<p class="sub">Look at the pattern and choose what comes next!</p>

<div id="startScreen" class="start-box">
<h2 style="margin-bottom:15px;">Ready to Play?</h2>
<p style="color:#64748b;margin-bottom:25px;">Click Start to test your pattern skills!</p>
<button class="btn" id="startBtn">Start Game</button>
</div>

<div class="stats hidden" id="statsBar">
<div class="stat"><span>Level</span><strong id="pLevel">1</strong></div>
<div class="stat"><span>Score</span><strong id="pScore">0</strong></div>
</div>

<div class="display hidden" id="pDisplay">?</div>
<div class="opts hidden" id="pOptions"></div>
<button class="btn hidden" id="restartBtn">Restart</button>
</div>
</div>

<script>
var audioCtx=null,bgInterval=null,isMuted=localStorage.getItem("gameSoundMuted")==="true";
var pLv=1,pSc=0,correct="",started=false,totalC=0,totalA=0;
var patterns=[
{s:["O","X","O","X","?"],a:"O",o:["O","X","Z"]},
{s:["*","+","*","+","?"],a:"*",o:["+","*","$"]},
{s:["^","^","O","^","^","O","?"],a:"^",o:["O","^","Z"]},
{s:["A","B","C","A","B","?"],a:"C",o:["A","C","B"]}
];

function unlock(){if(audioCtx)return;try{audioCtx=new(window.AudioContext||window.webkitAudioContext)();if(audioCtx.state==="suspended")audioCtx.resume();}catch(e){}}
function tone(f,d,t,v){if(isMuted||!audioCtx)return;try{var o=audioCtx.createOscillator(),g=audioCtx.createGain();o.type=t||"sine";o.frequency.setValueAtTime(f,audioCtx.currentTime);g.gain.setValueAtTime(v||0.15,audioCtx.currentTime);g.gain.exponentialRampToValueAtTime(0.001,audioCtx.currentTime+d);o.connect(g);g.connect(audioCtx.destination);o.start(audioCtx.currentTime);o.stop(audioCtx.currentTime+d);}catch(e){}}
function speak(t){if(isMuted||!("speechSynthesis"in window))return;window.speechSynthesis.cancel();var u=new SpeechSynthesisUtterance(t);u.lang="en-IN";u.rate=0.9;window.speechSynthesis.speak(u);}
function lvlSfx(){tone(523,0.12,"sine",0.18);setTimeout(function(){tone(659,0.12,"sine",0.18)},130);setTimeout(function(){tone(784,0.12,"sine",0.18)},260);setTimeout(function(){tone(1047,0.25,"sine",0.2)},390);}
function wrongSfx(){tone(200,0.25,"sawtooth",0.12);}
function winSfx(){[523,659,784,1047,784,1047,1319].forEach(function(n,i){setTimeout(function(){tone(n,0.18,"sine",0.18)},i*120)});}
function stopBG(){if(bgInterval){clearInterval(bgInterval);bgInterval=null;}}
function startBG(){if(isMuted)return;var n=[262,294,330,349,392,349,330,294],idx=0;function p(){if(isMuted||!audioCtx)return;try{var o=audioCtx.createOscillator(),g=audioCtx.createGain();o.type="sine";o.frequency.setValueAtTime(n[idx%n.length],audioCtx.currentTime);g.gain.setValueAtTime(0.05,audioCtx.currentTime);g.gain.exponentialRampToValueAtTime(0.001,audioCtx.currentTime+0.45);o.connect(g);g.connect(audioCtx.destination);o.start(audioCtx.currentTime);o.stop(audioCtx.currentTime+0.45);idx++;}catch(e){}}p();bgInterval=setInterval(p,500);}

function startGame(){
    unlock();stopBG();pLv=1;pSc=0;totalC=0;totalA=0;
    document.getElementById("pLevel").textContent="1";document.getElementById("pScore").textContent="0";
    document.getElementById("startScreen").classList.add("hidden");
    document.getElementById("statsBar").classList.remove("hidden");
    document.getElementById("pDisplay").classList.remove("hidden");
    document.getElementById("pOptions").classList.remove("hidden");
    document.getElementById("restartBtn").classList.remove("hidden");
    showPattern();
}

function showPattern(){
    started=true;
    var idx=(pLv-1)%patterns.length;var p=patterns[idx];correct=p.a;
    document.getElementById("pDisplay").textContent=p.s.join("   ");
    speak("Look carefully and choose what comes next");
    setTimeout(startBG,1000);
    var oc=document.getElementById("pOptions");oc.innerHTML="";
    p.o.forEach(function(opt){
        var b=document.createElement("button");b.className="opt";b.textContent=opt;
        b.onclick=function(){checkAnswer(opt);};oc.appendChild(b);
    });
}

function checkAnswer(sel){
    if(!started)return;started=false;totalA++;stopBG();
    if(sel===correct){lvlSfx();pSc+=10;totalC++;pLv++;document.getElementById("pScore").textContent=pSc;document.getElementById("pLevel").textContent=pLv;speak("Correct!");}
    else{wrongSfx();speak("Not quite! The answer was "+correct);}
    setTimeout(showPattern,1500);
}

window.onload=function(){
    document.getElementById("startBtn").onclick=function(){startGame();};
    document.getElementById("restartBtn").onclick=function(){startGame();};
};
</script>
</body>
</html>
