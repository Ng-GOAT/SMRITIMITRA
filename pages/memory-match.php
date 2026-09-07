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
    <title>Memory Match | SmritiMitra</title>
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
                <h1>&#x1F9E0; Memory Match</h1>
                <p>Find matching pairs and strengthen your memory.</p>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
                <a href="games.php" class="back-link">&larr; Back to Games</a>
            </div>
        </section>

        <div id="startScreen" class="hero-card" style="text-align:center;">
            <div style="font-size:80px;margin-bottom:15px;">&#x1F9E0;</div>
            <h2 style="margin-bottom:10px;">Ready to Play?</h2>
            <p style="color:#64748b;margin-bottom:25px;">Find matching pairs to win! Listen for sounds and voice guidance.</p>
            <button id="startBtn" class="restart-game-btn" style="padding:18px 50px;font-size:18px;">&#9654; Start Game</button>
        </div>

        <div class="game-stats" id="gameStats" style="display:none;">
            <div class="game-stat-card"><span>&#x1F3AF; Moves</span><strong id="moves">0</strong></div>
            <div class="game-stat-card"><span>&#x1F9E9; Matches</span><strong id="matches">0 / 4</strong></div>
        </div>

        <div class="memory-game-container" id="gameArea" style="display:none;">
            <div class="memory-board" id="memoryBoard"></div>
        </div>

        <div class="game-actions" id="gameActions" style="display:none;">
            <button class="restart-game-btn" id="restartBtn">&#x1F504; Restart Game</button>
        </div>

        <div id="gameResult" class="game-result hidden">
            <h2>&#x1F389; Excellent!</h2>
            <p id="resultText"></p>
            <button class="restart-game-btn" id="playAgainBtn">Play Again</button>
        </div>
    </main>
</div>

<script>
var audioCtx=null,bgInterval=null,isMuted=localStorage.getItem("gameSoundMuted")==="true";

function unlock(){if(audioCtx)return;try{audioCtx=new(window.AudioContext||window.webkitAudioContext)();if(audioCtx.state==="suspended")audioCtx.resume();}catch(e){}}
document.addEventListener("click",unlock,{once:true});

function tone(f,d,t,v){if(isMuted||!audioCtx)return;try{var o=audioCtx.createOscillator(),g=audioCtx.createGain();o.type=t||"sine";o.frequency.setValueAtTime(f,audioCtx.currentTime);g.gain.setValueAtTime(v||0.15,audioCtx.currentTime);g.gain.exponentialRampToValueAtTime(0.001,audioCtx.currentTime+d);o.connect(g);g.connect(audioCtx.destination);o.start(audioCtx.currentTime);o.stop(audioCtx.currentTime+d);}catch(e){}}
function speak(t){if(isMuted||!("speechSynthesis"in window))return;window.speechSynthesis.cancel();var u=new SpeechSynthesisUtterance(t);u.lang="en-IN";u.rate=0.9;u.pitch=1.1;window.speechSynthesis.speak(u);}
function flipSfx(){tone(440,0.07,"sine",0.12);}
function matchSfx(){tone(523,0.12,"sine",0.2);setTimeout(function(){tone(659,0.12,"sine",0.2)},120);setTimeout(function(){tone(784,0.18,"sine",0.2)},240);}
function wrongSfx(){tone(200,0.25,"sawtooth",0.12);}
function winSfx(){[523,659,784,1047,784,1047,1319].forEach(function(n,i){setTimeout(function(){tone(n,0.18,"sine",0.18)},i*120)});}
function stopBG(){if(bgInterval){clearInterval(bgInterval);bgInterval=null;}}
function startBG(){if(isMuted)return;var n=[262,294,330,349,392,349,330,294],idx=0;function p(){if(isMuted||!audioCtx)return;try{var o=audioCtx.createOscillator(),g=audioCtx.createGain();o.type="sine";o.frequency.setValueAtTime(n[idx%n.length],audioCtx.currentTime);g.gain.setValueAtTime(0.05,audioCtx.currentTime);g.gain.exponentialRampToValueAtTime(0.001,audioCtx.currentTime+0.45);o.connect(g);g.connect(audioCtx.destination);o.start(audioCtx.currentTime);o.stop(audioCtx.currentTime+0.45);idx++;}catch(e){}}p();bgInterval=setInterval(p,500);}

var E={"A":"\uD83C\uDF4E","B":"\uD83D\uDC36","C":"\uD83C\uDF38","D":"\uD83D\uDE97"};
var cards=[],fCard=null,sCard=null,moves=0,matches=0,locked=false,totalAttempts=0;

function startGame(){
    unlock();stopBG();
    cards=["A","A","B","B","C","C","D","D"].sort(function(){return Math.random()-0.5});
    fCard=null;sCard=null;moves=0;matches=0;locked=false;totalAttempts=0;
    document.getElementById("moves").textContent="0";
    document.getElementById("matches").textContent="0 / 4";
    document.getElementById("startScreen").style.display="none";
    document.getElementById("gameStats").style.display="grid";
    document.getElementById("gameArea").style.display="block";
    document.getElementById("gameActions").style.display="flex";
    document.getElementById("gameResult").classList.add("hidden");
    var b=document.getElementById("memoryBoard");b.innerHTML="";
    cards.forEach(function(v){
        var c=document.createElement("div");c.classList.add("memory-card");
        c.setAttribute("data-v",v);c.textContent="?";
        c.addEventListener("click",function(){flip(c);});
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
    else{sCard=c;moves++;totalAttempts++;document.getElementById("moves").textContent=moves;check();}
}

function check(){
    locked=true;
    if(fCard.getAttribute("data-v")===sCard.getAttribute("data-v")){
        matchSfx();fCard.classList.add("matched");sCard.classList.add("matched");matches++;
        document.getElementById("matches").textContent=matches+" / 4";
        fCard=null;sCard=null;locked=false;
        if(matches===4){
            stopBG();setTimeout(function(){
                winSfx();speak("Hurray! You won! Thats amazing!");
                document.getElementById("gameResult").classList.remove("hidden");
                document.getElementById("resultText").textContent="You completed the game in "+moves+" moves!";
                saveScore(moves,totalAttempts);
            },500);
        }
    }else{
        wrongSfx();
        setTimeout(function(){fCard.classList.remove("flipped");sCard.classList.remove("flipped");fCard.textContent="?";sCard.textContent="?";fCard=null;sCard=null;locked=false;},800);
    }
}

function saveScore(score,attempts){
    var acc=Math.round((matches*2/Math.max(attempts,1))*100);
    fetch("/SmritiMitra/api/games.php",{method:"POST",body:new URLSearchParams({action:"save_score",game_type:"memory_match",score:Math.max(0,100-score*2),level:1,difficulty:"easy",accuracy:acc})}).catch(function(){});
}

window.onload=function(){
    document.getElementById("startBtn").onclick=function(){startGame();};
    document.getElementById("restartBtn").onclick=function(){startGame();};
    document.getElementById("playAgainBtn").onclick=function(){startGame();};
};
</script>
</body>
</html>
