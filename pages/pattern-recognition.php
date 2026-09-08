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
    <title>Pattern Recognition | SmritiMitra</title>
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
                <h1>&#x1F9E9; Pattern Recognition</h1>
                <p>Look at the pattern and choose what comes next!</p>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
                <a href="games.php" class="back-link">&larr; Back to Games</a>
            </div>
        </section>

        <div id="startScreen" class="hero-card" style="text-align:center;">
            <div style="font-size:80px;margin-bottom:15px;">&#x1F9E9;</div>
            <h2 style="margin-bottom:10px;">Ready to Play?</h2>
            <p style="color:#64748b;margin-bottom:25px;">Click Start to test your pattern skills!</p>
            <button id="startBtn" class="restart-game-btn" style="padding:18px 50px;font-size:18px;">&#9654; Start Game</button>
        </div>

        <div class="game-stats" id="gameStats" style="display:none;">
            <div class="game-stat-card"><span>&#x1F3C6; Level</span><strong id="pLevel">1</strong></div>
            <div class="game-stat-card"><span>&#x1F3AF; Score</span><strong id="pScore">0</strong></div>
        </div>

        <div class="pattern-game-container" id="gameArea" style="display:none;">
            <div style="text-align:center;font-size:36px;font-weight:700;background:white;padding:40px;border-radius:20px;box-shadow:0 4px 15px rgba(0,0,0,0.05);margin-bottom:25px;" id="pDisplay">?</div>
            <div style="display:flex;gap:15px;justify-content:center;margin-bottom:25px;" id="pOptions"></div>
        </div>

        <div class="game-actions" id="gameActions" style="display:none;justify-content:center;">
            <button class="restart-game-btn" id="restartBtn">&#x1F504; Restart</button>
        </div>
    </main>
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
document.addEventListener("click",unlock,{once:true});
function tone(f,d,t,v){if(isMuted||!audioCtx)return;try{var o=audioCtx.createOscillator(),g=audioCtx.createGain();o.type=t||"sine";o.frequency.setValueAtTime(f,audioCtx.currentTime);g.gain.setValueAtTime(v||0.15,audioCtx.currentTime);g.gain.exponentialRampToValueAtTime(0.001,audioCtx.currentTime+d);o.connect(g);g.connect(audioCtx.destination);o.start(audioCtx.currentTime);o.stop(audioCtx.currentTime+d);}catch(e){}}
function speak(t){if(isMuted||!("speechSynthesis"in window))return;window.speechSynthesis.cancel();var u=new SpeechSynthesisUtterance(t);u.lang="en-IN";u.rate=0.9;window.speechSynthesis.speak(u);}
function lvlSfx(){tone(523,0.12,"sine",0.18);setTimeout(function(){tone(659,0.12,"sine",0.18)},130);setTimeout(function(){tone(784,0.12,"sine",0.18)},260);setTimeout(function(){tone(1047,0.25,"sine",0.2)},390);}
function wrongSfx(){tone(200,0.25,"sawtooth",0.12);}
function stopBG(){if(bgInterval){clearInterval(bgInterval);bgInterval=null;}}
function startBG(){if(isMuted)return;var n=[262,294,330,349,392,349,330,294],idx=0;function p(){if(isMuted||!audioCtx)return;try{var o=audioCtx.createOscillator(),g=audioCtx.createGain();o.type="sine";o.frequency.setValueAtTime(n[idx%n.length],audioCtx.currentTime);g.gain.setValueAtTime(0.05,audioCtx.currentTime);g.gain.exponentialRampToValueAtTime(0.001,audioCtx.currentTime+0.45);o.connect(g);g.connect(audioCtx.destination);o.start(audioCtx.currentTime);o.stop(audioCtx.currentTime+0.45);idx++;}catch(e){}}p();bgInterval=setInterval(p,500);}

function startGame(){
    unlock();stopBG();pLv=1;pSc=0;totalC=0;totalA=0;
    document.getElementById("pLevel").textContent="1";document.getElementById("pScore").textContent="0";
    document.getElementById("startScreen").style.display="none";
    document.getElementById("gameStats").style.display="grid";
    document.getElementById("gameArea").style.display="block";
    document.getElementById("gameActions").style.display="flex";
    showPattern();
}
function showPattern(){
    started=true;var idx=(pLv-1)%patterns.length;var p=patterns[idx];correct=p.a;
    document.getElementById("pDisplay").textContent=p.s.join("   ");
    speak("Look carefully and choose what comes next");setTimeout(startBG,1000);
    var oc=document.getElementById("pOptions");oc.innerHTML="";
    p.o.forEach(function(opt){
        var b=document.createElement("button");b.textContent=opt;
        b.style.cssText="background:#6d5dfc;color:white;border:none;border-radius:14px;padding:20px 30px;font-size:28px;cursor:pointer;";
        b.onmouseover=function(){b.style.background="#5b4cdb";};
        b.onmouseout=function(){b.style.background="#6d5dfc";};
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

    if(typeof VoiceControl!=="undefined"){
        VoiceControl.registerCommand("select_option",["select","option","choose","answer","pick"],
            "Select an answer option",function(t){
                var btns=document.querySelectorAll("#pOptions button");
                for(var i=0;i<btns.length;i++){
                    if(t.includes(btns[i].textContent.toLowerCase())){btns[i].click();return;}
                }
                var num=t.match(/\d+/);
                if(num){var idx=parseInt(num[0])-1;if(idx>=0&&idx<btns.length)btns[idx].click();}
            });
        VoiceControl.registerCommand("start_game",["start game","begin","play","start"],
            "Start the game",function(){startGame();});
    }
};
</script>
</body>
</html>
