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
<title>Routine Challenge</title>
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
.acts{display:flex;flex-wrap:wrap;gap:12px;margin-bottom:25px;}
.act{background:#fff;border:2px solid #e2e8f0;border-radius:14px;padding:16px 24px;cursor:pointer;font-size:16px;font-weight:600;}
.act:hover{border-color:#6d5dfc;}
.act.selected{background:#6d5dfc;color:white;border-color:#6d5dfc;}
.act:disabled{opacity:0.5;cursor:not-allowed;}
.order{background:#fff;border-radius:14px;padding:20px;box-shadow:0 4px 15px rgba(0,0,0,0.05);margin-bottom:25px;}
.order-item{padding:10px;border-bottom:1px solid #f1f5f9;font-size:15px;}
.btn{padding:16px 40px;background:linear-gradient(135deg,#6d5dfc,#8b5cf6);color:white;border:none;border-radius:12px;font-size:18px;font-weight:700;cursor:pointer;margin:10px;}
.start-box{text-align:center;padding:60px;background:#fff;border-radius:20px;box-shadow:0 8px 25px rgba(0,0,0,0.05);margin-bottom:25px;}
.hidden{display:none;}
</style>
</head>
<body>
<div class="layout">
<div class="sidebar"><h2>SmritiMitra</h2><p style="color:#94a3b8;margin-top:5px;">AI Cognitive Care</p><hr style="margin:20px 0;"><a href="/SmritiMitra/pages/games.php" style="color:#6d5dfc;text-decoration:none;">Back to Games</a></div>
<div class="main">
<h1>Routine Challenge</h1>
<p class="sub">Arrange daily activities in the correct order!</p>

<div id="startScreen" class="start-box">
<h2 style="margin-bottom:15px;">Ready to Play?</h2>
<p style="color:#64748b;margin-bottom:25px;">Click Start to test your daily routine memory!</p>
<button class="btn" id="startBtn">Start Game</button>
</div>

<div class="stats hidden" id="statsBar">
<div class="stat"><span>Score</span><strong id="rScore">0</strong></div>
<div class="stat"><span>Selected</span><strong id="rProgress">0 / 5</strong></div>
</div>

<div class="acts hidden" id="rItems"></div>
<div class="order hidden" id="rOrder"><p style="color:#94a3b8;margin-bottom:10px;">Your Daily Routine:</p><div id="rList" style="color:#64748b;">Choose activities in correct order</div></div>
<button class="btn hidden" id="clearBtn">Clear</button>
<button class="btn hidden" id="restartBtn">Restart</button>
</div>
</div>

<script>
var audioCtx=null,bgInterval=null,isMuted=localStorage.getItem("gameSoundMuted")==="true";
var acts=[
{id:1,name:"Wake Up",order:1},{id:2,name:"Brush Teeth",order:2},{id:3,name:"Breakfast",order:3},
{id:4,name:"Take Medicine",order:4},{id:5,name:"Morning Walk",order:5}
];
var selected=[],rSc=0,rStarted=false;

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
    unlock();stopBG();selected=[];rSc=0;rStarted=true;
    document.getElementById("rScore").textContent="0";document.getElementById("rProgress").textContent="0 / 5";
    document.getElementById("startScreen").classList.add("hidden");
    document.getElementById("statsBar").classList.remove("hidden");
    document.getElementById("rItems").classList.remove("hidden");
    document.getElementById("rOrder").classList.remove("hidden");
    document.getElementById("clearBtn").classList.remove("hidden");
    document.getElementById("restartBtn").classList.remove("hidden");
    document.getElementById("rList").innerHTML="Choose activities in correct order";
    speak("Select the activities in the correct daily order");
    setTimeout(startBG,1000);
    var container=document.getElementById("rItems");container.innerHTML="";
    var shuffled=acts.slice().sort(function(){return Math.random()-0.5;});
    shuffled.forEach(function(a){
        var b=document.createElement("button");b.className="act";b.textContent=a.name;b.setAttribute("data-id",a.id);
        b.onclick=function(){selectAct(a,b);};container.appendChild(b);
    });
}

function selectAct(a,b){
    if(!rStarted||selected.some(function(s){return s.id===a.id;}))return;
    clickSfx();selected.push(a);b.classList.add("selected");b.disabled=true;
    document.getElementById("rProgress").textContent=selected.length+" / 5";
    var list=document.getElementById("rList");list.innerHTML="";
    selected.forEach(function(s,i){list.innerHTML+='<div class="order-item">'+(i+1)+". "+s.name+"</div>";});
    if(selected.length===acts.length)checkOrder();
}

function checkOrder(){
    rStarted=false;stopBG();
    var c=0;selected.forEach(function(s,i){if(s.order===i+1)c++;});
    rSc+=c*10;document.getElementById("rScore").textContent=rSc;
    if(c===5){winSfx();speak("Excellent! You got them all right!");}
    else if(c>=3){lvlSfx();speak("Good job! You got "+c+" out of 5 correct.");}
    else{wrongSfx();speak("Try again! You got "+c+" out of 5 correct.");}
}

window.onload=function(){
    document.getElementById("startBtn").onclick=function(){startGame();};
    document.getElementById("restartBtn").onclick=function(){startGame();};
    document.getElementById("clearBtn").onclick=function(){startGame();};
};
</script>
</body>
</html>
