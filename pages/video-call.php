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
    <title>Emergency Video Call | SmritiMitra</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .call-container { display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:70vh;gap:20px; }
        .video-wrapper { position:relative;width:100%;max-width:600px;border-radius:20px;overflow:hidden;background:#000;box-shadow:0 20px 60px rgba(0,0,0,0.3); }
        .video-wrapper video { width:100%;border-radius:20px;transform:scaleX(-1); }
        .call-status { font-size:18px;font-weight:600;color:#64748b;text-align:center; }
        .call-status.active { color:#22c55e; }
        .call-status.ringing { color:#f59e0b;animation:pulse 1.5s infinite; }
        .call-status.ended { color:#dc2626; }
        .call-actions { display:flex;gap:15px;margin-top:10px; }
        .call-btn { width:70px;height:70px;border-radius:50%;border:none;font-size:28px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.3s ease; }
        .call-btn:hover { transform:scale(1.1); }
        .call-btn.call { background:#22c55e;color:white; }
        .call-btn.end-call { background:#dc2626;color:white; }
        .call-btn.mute { background:#f1f5f9;color:#475569; }
        .call-btn.camera-switch { background:#f1f5f9;color:#475569; }
        .emergency-alert { position:fixed;top:20px;left:50%;transform:translateX(-50%);background:#dc2626;color:white;padding:15px 30px;border-radius:12px;font-weight:700;font-size:16px;z-index:99999;display:none;animation:shake 0.5s; }
        @keyframes pulse { 0%,100%{opacity:1}50%{opacity:0.5} }
        @keyframes shake { 0%,100%{transform:translateX(-50%) rotate(0)}25%{transform:translateX(-50%) rotate(-2deg)}75%{transform:translateX(-50%) rotate(2deg)} }
        .caregiver-list { width:100%;max-width:600px;margin-top:20px; }
        .caregiver-item { display:flex;align-items:center;gap:15px;background:white;padding:15px 20px;border-radius:14px;box-shadow:0 4px 15px rgba(0,0,0,0.05);margin-bottom:10px;cursor:pointer;transition:all 0.3s ease; }
        .caregiver-item:hover { transform:translateY(-2px);box-shadow:0 6px 20px rgba(0,0,0,0.1); }
        .caregiver-avatar { width:50px;height:50px;border-radius:50%;background:#6d5dfc;color:white;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700; }
        .caregiver-info h3 { margin:0;font-size:16px; }
        .caregiver-info p { margin:0;color:#64748b;font-size:14px; }
        .no-caregiver { text-align:center;padding:40px;color:#64748b; }
        .no-caregiver .icon { font-size:60px;margin-bottom:15px; }
    </style>
</head>
<body>
<div class="app-layout">
    <?php include "../includes/sidebar.php"; ?>
    <main class="main-content">
        <?php include "../includes/header.php"; ?>

        <section class="game-page-header">
            <div>
                <p class="section-tag">EMERGENCY</p>
                <h1>&#x1F4DE; Video Call</h1>
                <p>Start a video call with your caregiver.</p>
            </div>
        </section>

        <div id="emergencyAlert" class="emergency-alert">&#x26A0;&#xFE0F; Emergency call started! Notifying caregiver...</div>

        <div class="call-container">
            <div class="call-status" id="callStatus">Select a caregiver to start a video call</div>

            <div class="video-wrapper" id="videoWrapper" style="display:none;">
                <video id="localVideo" autoplay muted playsinline></video>
            </div>

            <div class="call-actions" id="callActions" style="display:none;">
                <button class="call-btn mute" id="muteBtn" onclick="toggleMute()">&#x1F507;</button>
                <button class="call-btn end-call" id="endCallBtn" onclick="endCall()">&#x1F534;</button>
                <button class="call-btn camera-switch" id="switchBtn" onclick="switchCamera()">&#x1F4F7;</button>
            </div>

            <div class="caregiver-list" id="caregiverList">
                <div class="no-caregiver">
                    <div class="icon">&#x1F465;</div>
                    <h3>Loading caregivers...</h3>
                    <p>Please wait</p>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
var localStream = null;
var currentCall = null;
var isMuted = false;
var currentCamera = 'user';
var audioCtx = null;

function unlock(){if(audioCtx)return;try{audioCtx=new(window.AudioContext||window.webkitAudioContext)();if(audioCtx.state==="suspended")audioCtx.resume();}catch(e){}}
document.addEventListener("click",unlock,{once:true});

function tone(f,d,t,v){if(!audioCtx)return;try{var o=audioCtx.createOscillator(),g=audioCtx.createGain();o.type=t||"sine";o.frequency.setValueAtTime(f,audioCtx.currentTime);g.gain.setValueAtTime(v||0.15,audioCtx.currentTime);g.gain.exponentialRampToValueAtTime(0.001,audioCtx.currentTime+d);o.connect(g);g.connect(audioCtx.destination);o.start(audioCtx.currentTime);o.stop(audioCtx.currentTime+d);}catch(e){}}

function speak(t){if(!("speechSynthesis"in window))return;window.speechSynthesis.cancel();var u=new SpeechSynthesisUtterance(t);u.lang="en-IN";u.rate=0.9;window.speechSynthesis.speak(u);}

function ringTone(){
    var freqs=[440,480,440,480];
    var i=0;
    function play(){
        if(i>=freqs.length||currentCall)return;
        tone(freqs[i],0.3,"sine",0.12);
        i++;
        setTimeout(play,400);
    }
    play();
}

function loadCaregivers(){
    fetch('/SmritiMitra/api/caregiver.php?action=get_links')
    .then(r=>r.json())
    .then(data=>{
        var list=document.getElementById('caregiverList');
        var links=data.links||data.caregivers||[];
        if(links.length===0){
            list.innerHTML='<div class="no-caregiver"><div class="icon">&#x1F465;</div><h3>No caregivers linked</h3><p>Ask your caregiver to link with you from their account.</p></div>';
            return;
        }
        list.innerHTML='';
        links.forEach(function(link){
            var name=link.caregiver_name||link.name||'Caregiver';
            var initial=name.charAt(0).toUpperCase();
            var item=document.createElement('div');
            item.className='caregiver-item';
            item.innerHTML='<div class="caregiver-avatar">'+initial+'</div><div class="caregiver-info"><h3>'+name+'</h3><p>Tap to start video call</p></div>';
            item.onclick=function(){startCall(link.caregiver_id||link.caregiver_user_id,name);};
            list.appendChild(item);
        });
    })
    .catch(function(){
        document.getElementById('caregiverList').innerHTML='<div class="no-caregiver"><div class="icon">&#x26A0;&#xFE0F;</div><h3>Could not load caregivers</h3><p>Please check your connection.</p></div>';
    });
}

async function startCall(caregiverId,caregiverName){
    unlock();
    tone(800,0.15,"sine",0.15);
    setTimeout(function(){tone(1000,0.15,"sine",0.15)},150);

    document.getElementById('caregiverList').style.display='none';
    document.getElementById('videoWrapper').style.display='block';
    document.getElementById('callActions').style.display='flex';
    document.getElementById('callStatus').textContent='Calling '+caregiverName+'...';
    document.getElementById('callStatus').className='call-status ringing';

    try{
        localStream=await navigator.mediaDevices.getUserMedia({video:{facingMode:currentCamera},audio:true});
        document.getElementById('localVideo').srcObject=localStream;
        ringTone();
    }catch(err){
        alert('Could not access camera/microphone. Please allow permissions.');
        endCall();
        return;
    }

    currentCall={id:caregiverId,name:caregiverName};

    fetch('/SmritiMitra/api/emergency.php',{
        method:'POST',
        body:new URLSearchParams({action:'notify_caregiver',caregiver_user_id:caregiverId})
    }).catch(function(){});

    document.getElementById('emergencyAlert').style.display='block';
    speak('Emergency call started. Notifying '+caregiverName);

    setTimeout(function(){
        if(currentCall){
            document.getElementById('callStatus').textContent='Connected to '+caregiverName;
            document.getElementById('callStatus').className='call-status active';
        }
    },3000);
}

function endCall(){
    if(localStream){
        localStream.getTracks().forEach(function(t){t.stop();});
        localStream=null;
    }
    document.getElementById('localVideo').srcObject=null;
    document.getElementById('videoWrapper').style.display='none';
    document.getElementById('callActions').style.display='none';
    document.getElementById('caregiverList').style.display='block';
    document.getElementById('callStatus').textContent='Call ended';
    document.getElementById('callStatus').className='call-status ended';
    document.getElementById('emergencyAlert').style.display='none';
    currentCall=null;
    tone(300,0.3,"sine",0.1);
    setTimeout(function(){document.getElementById('callStatus').textContent='Select a caregiver to start a video call';document.getElementById('callStatus').className='call-status';},2000);
}

function toggleMute(){
    if(!localStream)return;
    isMuted=!isMuted;
    localStream.getAudioTracks().forEach(function(t){t.enabled=!isMuted;});
    document.getElementById('muteBtn').innerHTML=isMuted?'&#x1F50A;':'&#x1F507;';
}

async function switchCamera(){
    currentCamera=currentCamera==='user'?'environment':'user';
    if(localStream){localStream.getTracks().forEach(function(t){t.stop();});}
    try{
        localStream=await navigator.mediaDevices.getUserMedia({video:{facingMode:currentCamera},audio:true});
        document.getElementById('localVideo').srcObject=localStream;
    }catch(e){}
}

window.onload=function(){loadCaregivers();};
window.onbeforeunload=function(){if(currentCall)endCall();};
</script>
</body>
</html>
