<?php
session_start();
include "../config/db.php";
requireLogin();
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Number Sequence | SmritiMitra</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-layout">
    <?php include "../includes/sidebar.php"; ?>
    <main class="main-content">
        <?php include "../includes/header.php"; ?>

        <section class="game-page-header">
            <div>
                <p class="section-tag">COGNITIVE TRAINING</p>
                <h1>&#x1F522; Number Sequence</h1>
                <p>Remember the numbers and repeat the sequence!</p>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
                <a href="games.php" class="back-link">&larr; Back to Games</a>
            </div>
        </section>

        <div id="startScreen" class="hero-card" style="text-align:center;">
            <div style="font-size:80px;margin-bottom:15px;">&#x1F522;</div>
            <h2 style="margin-bottom:10px;">Ready to Play?</h2>
            <p style="color:#64748b;margin-bottom:25px;">Click Start to test your number memory!</p>
            <button id="startBtn" class="restart-game-btn" style="padding:18px 50px;font-size:18px;">&#9654; Start Game</button>
        </div>

        <div class="game-stats" id="gameStats" style="display:none;">
            <div class="game-stat-card"><span>&#x1F3C6; Level</span><strong id="level">1</strong></div>
            <div class="game-stat-card"><span>&#x1F3AF; Score</span><strong id="score">0</strong></div>
        </div>

        <div class="sequence-game-container" id="gameArea" style="display:none;">
            <p class="sequence-stats" id="instruction" style="text-align:center;color:#64748b;margin-bottom:15px;">Remember the numbers...</p>
            <div class="sequence-display" id="display" style="text-align:center;font-size:36px;font-weight:700;background:white;padding:40px;border-radius:20px;box-shadow:0 4px 15px rgba(0,0,0,0.05);margin-bottom:25px;">?</div>
            <div class="number-buttons" id="numBtns" style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;max-width:350px;margin:0 auto 25px;">
                <button class="num-btn" data-n="1" style="background:#6d5dfc;color:white;border:none;border-radius:14px;padding:20px;font-size:24px;font-weight:700;cursor:pointer;">1</button>
                <button class="num-btn" data-n="2" style="background:#6d5dfc;color:white;border:none;border-radius:14px;padding:20px;font-size:24px;font-weight:700;cursor:pointer;">2</button>
                <button class="num-btn" data-n="3" style="background:#6d5dfc;color:white;border:none;border-radius:14px;padding:20px;font-size:24px;font-weight:700;cursor:pointer;">3</button>
                <button class="num-btn" data-n="4" style="background:#6d5dfc;color:white;border:none;border-radius:14px;padding:20px;font-size:24px;font-weight:700;cursor:pointer;">4</button>
                <button class="num-btn" data-n="5" style="background:#6d5dfc;color:white;border:none;border-radius:14px;padding:20px;font-size:24px;font-weight:700;cursor:pointer;">5</button>
                <button class="num-btn" data-n="6" style="background:#6d5dfc;color:white;border:none;border-radius:14px;padding:20px;font-size:24px;font-weight:700;cursor:pointer;">6</button>
                <button class="num-btn" data-n="7" style="background:#6d5dfc;color:white;border:none;border-radius:14px;padding:20px;font-size:24px;font-weight:700;cursor:pointer;">7</button>
                <button class="num-btn" data-n="8" style="background:#6d5dfc;color:white;border:none;border-radius:14px;padding:20px;font-size:24px;font-weight:700;cursor:pointer;">8</button>
                <button class="num-btn" data-n="9" style="background:#6d5dfc;color:white;border:none;border-radius:14px;padding:20px;font-size:24px;font-weight:700;cursor:pointer;">9</button>
            </div>
            <div style="text-align:center;margin-bottom:15px;"><p style="color:#94a3b8;margin-bottom:5px;">Your answer:</p><div id="answer" style="font-size:24px;font-weight:700;min-height:40px;">-</div></div>
        </div>

        <div class="game-actions" id="gameActions" style="display:none;justify-content:center;">
            <button class="restart-game-btn" id="restartBtn">&#x1F504; Restart</button>
        </div>
    </main>
</div>

<script>
var audioCtx=null,bgInterval=null,isMuted=localStorage.getItem("gameSoundMuted")==="true";
var seq=[],ans=[],lv=1,sc=0,active=false,totalC=0,totalA=0;

function unlock(){if(audioCtx)return;try{audioCtx=new(window.AudioContext||window.webkitAudioContext)();if(audioCtx.state==="suspended")audioCtx.resume();}catch(e){}}
document.addEventListener("click",unlock,{once:true});
function tone(f,d,t,v){if(isMuted||!audioCtx)return;try{var o=audioCtx.createOscillator(),g=audioCtx.createGain();o.type=t||"sine";o.frequency.setValueAtTime(f,audioCtx.currentTime);g.gain.setValueAtTime(v||0.15,audioCtx.currentTime);g.gain.exponentialRampToValueAtTime(0.001,audioCtx.currentTime+d);o.connect(g);g.connect(audioCtx.destination);o.start(audioCtx.currentTime);o.stop(audioCtx.currentTime+d);}catch(e){}}
function speak(t){if(isMuted||!("speechSynthesis"in window))return;window.speechSynthesis.cancel();var u=new SpeechSynthesisUtterance(t);u.lang="en-IN";u.rate=0.9;window.speechSynthesis.speak(u);}
function clickSfx(){tone(800,0.08,"sine",0.12);}
function lvlSfx(){tone(523,0.12,"sine",0.18);setTimeout(function(){tone(659,0.12,"sine",0.18)},130);setTimeout(function(){tone(784,0.12,"sine",0.18)},260);setTimeout(function(){tone(1047,0.25,"sine",0.2)},390);}
function wrongSfx(){tone(200,0.25,"sawtooth",0.12);}
function stopBG(){if(bgInterval){clearInterval(bgInterval);bgInterval=null;}}
function startBG(){if(isMuted)return;var n=[262,294,330,349,392,349,330,294],idx=0;function p(){if(isMuted||!audioCtx)return;try{var o=audioCtx.createOscillator(),g=audioCtx.createGain();o.type="sine";o.frequency.setValueAtTime(n[idx%n.length],audioCtx.currentTime);g.gain.setValueAtTime(0.05,audioCtx.currentTime);g.gain.exponentialRampToValueAtTime(0.001,audioCtx.currentTime+0.45);o.connect(g);g.connect(audioCtx.destination);o.start(audioCtx.currentTime);o.stop(audioCtx.currentTime+0.45);idx++;}catch(e){}}p();bgInterval=setInterval(p,500);}

function startGame(){
    unlock();stopBG();seq=[];ans=[];active=false;lv=1;sc=0;totalC=0;totalA=0;
    document.getElementById("level").textContent="1";document.getElementById("score").textContent="0";
    document.getElementById("startScreen").style.display="none";
    document.getElementById("gameStats").style.display="grid";
    document.getElementById("gameArea").style.display="block";
    document.getElementById("gameActions").style.display="flex";
    document.getElementById("answer").textContent="-";
    showSeq();
}
function showSeq(){
    active=false;ans=[];document.getElementById("answer").textContent="-";
    var len=lv+2;seq=[];for(var i=0;i<len;i++)seq.push(Math.floor(Math.random()*9)+1);
    document.getElementById("display").textContent=seq.join("  ");
    document.getElementById("instruction").textContent="Remember the numbers...";
    speak("Remember the numbers carefully");setTimeout(startBG,1000);
    setTimeout(function(){document.getElementById("display").textContent="?";active=true;document.getElementById("instruction").textContent="Now repeat the sequence!";},3000);
}
function addNum(n){if(!active)return;clickSfx();ans.push(n);document.getElementById("answer").textContent=ans.join("  ");if(ans.length===seq.length)checkAns();}
function checkAns(){
    active=false;totalA++;stopBG();
    if(JSON.stringify(seq)===JSON.stringify(ans)){
        lvlSfx();sc+=10;totalC++;document.getElementById("score").textContent=sc;
        lv++;document.getElementById("level").textContent=lv;document.getElementById("instruction").textContent="Correct!";setTimeout(showSeq,1500);
    }else{wrongSfx();speak("Try again!");document.getElementById("instruction").textContent="Wrong! Try again.";setTimeout(showSeq,2500);}
}
window.onload=function(){
    document.getElementById("startBtn").onclick=function(){startGame();};
    document.getElementById("restartBtn").onclick=function(){startGame();};
    document.querySelectorAll(".num-btn").forEach(function(b){b.onclick=function(){addNum(parseInt(b.getAttribute("data-n")));};});
};
</script>
</body>
</html>
