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
    <title>Routine Challenge | SmritiMitra</title>
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
                <h1>&#x1F4CB; Routine Challenge</h1>
                <p>Put the daily tasks in the correct order!</p>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
                <a href="games.php" class="back-link">&larr; Back to Games</a>
            </div>
        </section>

        <div id="startScreen" class="hero-card" style="text-align:center;">
            <div style="font-size:80px;margin-bottom:15px;">&#x1F4CB;</div>
            <h2 style="margin-bottom:10px;">Ready to Play?</h2>
            <p style="color:#64748b;margin-bottom:25px;">Click Start to test your routine planning!</p>
            <button id="startBtn" class="restart-game-btn" style="padding:18px 50px;font-size:18px;">&#9654; Start Game</button>
        </div>

        <div class="game-stats" id="gameStats" style="display:none;">
            <div class="game-stat-card"><span>&#x1F3C6; Level</span><strong id="rLevel">1</strong></div>
            <div class="game-stat-card"><span>&#x1F3AF; Score</span><strong id="rScore">0</strong></div>
        </div>

        <div class="routine-game-container" id="gameArea" style="display:none;">
            <div id="rTask" style="text-align:center;font-size:28px;font-weight:700;background:white;padding:30px;border-radius:20px;box-shadow:0 4px 15px rgba(0,0,0,0.05);margin-bottom:25px;color:#6d5dfc;"></div>
            <div id="rList" style="display:flex;flex-direction:column;gap:10px;margin-bottom:25px;"></div>
        </div>

        <div class="game-actions" id="gameActions" style="display:none;justify-content:center;">
            <button class="restart-game-btn" id="restartBtn">&#x1F504; Restart</button>
        </div>
    </main>
</div>

<script>
var audioCtx=null,bgInterval=null,isMuted=localStorage.getItem("gameSoundMuted")==="true";
var tasks=[],totalC=0,totalA=0,rLv=1,rSc=0;

function unlock(){if(audioCtx)return;try{audioCtx=new(window.AudioContext||window.webkitAudioContext)();if(audioCtx.state==="suspended")audioCtx.resume();}catch(e){}}
document.addEventListener("click",unlock,{once:true});
function tone(f,d,t,v){if(isMuted||!audioCtx)return;try{var o=audioCtx.createOscillator(),g=audioCtx.createGain();o.type=t||"sine";o.frequency.setValueAtTime(f,audioCtx.currentTime);g.gain.setValueAtTime(v||0.15,audioCtx.currentTime);g.gain.exponentialRampToValueAtTime(0.001,audioCtx.currentTime+d);o.connect(g);g.connect(audioCtx.destination);o.start(audioCtx.currentTime);o.stop(audioCtx.currentTime+d);}catch(e){}}
function speak(t){if(isMuted||!("speechSynthesis"in window))return;window.speechSynthesis.cancel();var u=new SpeechSynthesisUtterance(t);u.lang="en-IN";u.rate=0.9;window.speechSynthesis.speak(u);}
function clickSfx(){tone(800,0.08,"sine",0.12);}
function lvlSfx(){tone(523,0.12,"sine",0.18);setTimeout(function(){tone(659,0.12,"sine",0.18)},130);setTimeout(function(){tone(784,0.12,"sine",0.18)},260);setTimeout(function(){tone(1047,0.25,"sine",0.2)},390);}
function wrongSfx(){tone(200,0.25,"sawtooth",0.12);}
function stopBG(){if(bgInterval){clearInterval(bgInterval);bgInterval=null;}}
function startBG(){if(isMuted)return;var n=[262,294,330,349,392,349,330,294],idx=0;function p(){if(isMuted||!audioCtx)return;try{var o=audioCtx.createOscillator(),g=audioCtx.createGain();o.type="sine";o.frequency.setValueAtTime(n[idx%n.length],audioCtx.currentTime);g.gain.setValueAtTime(0.05,audioCtx.currentTime);g.gain.exponentialRampToValueAtTime(0.001,audioCtx.currentTime+0.45);o.connect(g);g.connect(audioCtx.destination);o.start(audioCtx.currentTime);o.stop(audioCtx.currentTime+0.45);idx++;}catch(e){}}p();bgInterval=setInterval(p,500);}

var taskSets=[
    {title:"Morning Routine",tasks:["Wake Up","Brush Teeth","Take Medicine","Eat Breakfast","Start Day"]},
    {title:"Evening Routine",tasks:["Finish Work","Take Medicine","Eat Dinner","Watch TV","Sleep"]},
    {title:"Afternoon Routine",tasks:["Eat Lunch","Take Medicine","Rest","Go for Walk","Read Book"]},
    {title:"Daily Self-Care",tasks:["Wake Up","Drink Water","Wash Face","Eat Healthy","Exercise"]}
];

function startGame(){
    unlock();stopBG();totalC=0;totalA=0;rLv=1;rSc=0;
    document.getElementById("rLevel").textContent="1";document.getElementById("rScore").textContent="0";
    document.getElementById("startScreen").style.display="none";
    document.getElementById("gameStats").style.display="grid";
    document.getElementById("gameArea").style.display="block";
    document.getElementById("gameActions").style.display="flex";
    loadTask();
}
function loadTask(){
    var idx=(rLv-1)%taskSets.length;var ts=taskSets[idx];
    document.getElementById("rTask").textContent="Order the tasks: "+ts.title;
    speak("Put the tasks in the correct order: "+ts.title);
    setTimeout(startBG,1000);
    tasks=ts.tasks.slice().sort(function(){return Math.random()-0.5;});
    var list=document.getElementById("rList");list.innerHTML="";
    tasks.forEach(function(t){
        var d=document.createElement("div");
        d.className="routine-item";
        d.style.cssText="display:flex;align-items:center;justify-content:space-between;background:white;padding:15px 20px;border-radius:14px;box-shadow:0 4px 15px rgba(0,0,0,0.05);";
        d.innerHTML="<span style='font-size:18px;'>"+t+"</span><div style='display:flex;gap:8px;'>";
        if(tasks.indexOf(t)>0){var up=document.createElement("button");up.textContent="\u2191";up.style.cssText="background:#6d5dfc;color:white;border:none;border-radius:8px;padding:8px 14px;cursor:pointer;font-size:18px;";up.onclick=function(){moveItem(t,-1);};d.lastElementChild.appendChild(up);}
        if(tasks.indexOf(t)<tasks.length-1){var dn=document.createElement("button");dn.textContent="\u2193";dn.style.cssText="background:#8b5cf6;color:white;border:none;border-radius:8px;padding:8px 14px;cursor:pointer;font-size:18px;";dn.onclick=function(){moveItem(t,1);};d.lastElementChild.appendChild(dn);}
        list.appendChild(d);
    });
    var btn=document.createElement("button");btn.textContent="Done!";btn.className="restart-game-btn";btn.style.cssText="margin-top:20px;width:100%;padding:16px;";btn.onclick=checkOrder;list.appendChild(btn);
}
function moveItem(t,d){
    clickSfx();var i=tasks.indexOf(t);var ni=i+d;if(ni<0||ni>=tasks.length)return;var tmp=tasks[i];tasks[i]=tasks[ni];tasks[ni]=tmp;loadTask();
}
function checkOrder(){
    stopBG();totalA++;
    var orig=taskSets[(rLv-1)%taskSets.length].tasks;
    var correct=true;for(var i=0;i<tasks.length;i++){if(tasks[i]!==orig[i]){correct=false;break;}}
    if(correct){lvlSfx();rSc+=10;totalC++;rLv++;document.getElementById("rScore").textContent=rSc;document.getElementById("rLevel").textContent=rLv;speak("Well done! Next challenge!");}
    else{wrongSfx();speak("Not quite! Try again!");}
    setTimeout(loadTask,1500);
}
window.onload=function(){
    document.getElementById("startBtn").onclick=function(){startGame();};
    document.getElementById("restartBtn").onclick=function(){startGame();};

    if(typeof VoiceControl!=="undefined"){
        VoiceControl.registerCommand("move_up",["move up","up","shift up","go up"],
            "Move item up",function(){
                if(tasks.length>0)moveItem(tasks[0],-1);
            });
        VoiceControl.registerCommand("move_down",["move down","down","shift down","go down"],
            "Move item down",function(){
                if(tasks.length>0)moveItem(tasks[0],1);
            });
        VoiceControl.registerCommand("done",["done","submit","check","verify","finish"],
            "Check the order",function(){checkOrder();});
        VoiceControl.registerCommand("start_game",["start game","begin","play","start"],
            "Start the game",function(){startGame();});
        VoiceControl.speak("Routine Challenge loaded. Say start game to begin.");
    }
};
</script>
</body>
</html>
