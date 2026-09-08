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
    <title>Exercises | SmritiMitra</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .exercise-header { display:flex;justify-content:space-between;align-items:center;margin-bottom:30px; }
        .exercise-header h1 { font-size:32px;margin:6px 0; }
        .exercise-header p { color:#64748b; }
        .category-filters { display:flex;gap:10px;margin-bottom:25px;flex-wrap:wrap; }
        .category-btn { padding:10px 20px;border:2px solid #e2e8f0;border-radius:12px;background:white;cursor:pointer;font-weight:600;font-size:14px;transition:all 0.3s ease; }
        .category-btn:hover { border-color:#6d5dfc;color:#6d5dfc; }
        .category-btn.active { background:#6d5dfc;color:white;border-color:#6d5dfc; }
        .exercise-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(350px,1fr));gap:20px; }
        .exercise-card { background:white;border-radius:18px;overflow:hidden;box-shadow:0 5px 20px rgba(0,0,0,0.04);transition:all 0.3s ease; }
        .exercise-card:hover { transform:translateY(-5px);box-shadow:0 15px 35px rgba(0,0,0,0.1); }
        .video-container { position:relative;padding-bottom:56.25%;height:0;overflow:hidden;background:#000; }
        .video-container iframe { position:absolute;top:0;left:0;width:100%;height:100%;border:none; }
        .video-placeholder { position:absolute;top:0;left:0;width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#667eea,#764ba2);color:white;font-size:60px; }
        .exercise-info { padding:20px; }
        .exercise-meta { display:flex;gap:10px;margin-bottom:10px;flex-wrap:wrap; }
        .exercise-tag { padding:4px 10px;border-radius:8px;font-size:12px;font-weight:600; }
        .tag-category { background:#f0f0ff;color:#6d5dfc; }
        .tag-duration { background:#ecfdf5;color:#059669; }
        .tag-difficulty { background:#fef3c7;color:#d97706; }
        .exercise-info h3 { font-size:18px;margin-bottom:8px; }
        .exercise-info p { color:#64748b;font-size:14px;line-height:1.6;margin-bottom:15px; }
        .exercise-actions { display:flex;gap:10px; }
        .btn-watch { padding:10px 20px;background:#6d5dfc;color:white;border:none;border-radius:10px;font-weight:600;cursor:pointer;transition:all 0.3s ease; }
        .btn-watch:hover { background:#5b4cdb;transform:translateY(-2px); }
        .btn-delete { padding:10px 15px;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:10px;font-weight:600;cursor:pointer;transition:all 0.3s ease; }
        .btn-delete:hover { background:#fee2e2; }
        .add-exercise-btn { padding:12px 24px;background:linear-gradient(135deg,#6d5dfc,#8b5cf6);color:white;border:none;border-radius:12px;font-weight:700;cursor:pointer;font-size:14px;transition:all 0.3s ease; }
        .add-exercise-btn:hover { transform:translateY(-2px);box-shadow:0 8px 25px rgba(109,93,252,0.3); }
        .modal-overlay { display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;justify-content:center;align-items:center; }
        .modal-content { background:white;border-radius:20px;padding:30px;width:90%;max-width:550px;max-height:90vh;overflow-y:auto; }
        .form-group { margin-bottom:15px; }
        .form-group label { display:block;font-weight:600;margin-bottom:5px;font-size:14px; }
        .form-group input,.form-group textarea,.form-group select { width:100%;padding:12px;border:2px solid #e2e8f0;border-radius:10px;font-size:14px;box-sizing:border-box;font-family:inherit; }
        .form-group textarea { resize:vertical;min-height:100px; }
        .form-actions { display:flex;gap:10px;margin-top:20px; }
        .btn-save { flex:1;padding:12px;background:#6d5dfc;color:white;border:none;border-radius:10px;font-weight:700;cursor:pointer; }
        .btn-cancel { flex:1;padding:12px;background:#f1f5f9;border:none;border-radius:10px;font-weight:700;cursor:pointer; }
        .no-exercises { text-align:center;padding:60px 20px;color:#64748b; }
        .no-exercises .icon { font-size:60px;margin-bottom:15px; }
    </style>
</head>
<body>
<div class="app-layout">
    <?php include "../includes/sidebar.php"; ?>
    <main class="main-content">
        <?php include "../includes/header.php"; ?>

        <section class="exercise-header">
            <div>
                <p class="section-tag">THERAPY & EXERCISES</p>
                <h1>&#x1F9D8; Exercises</h1>
                <p>Acupressure and memory recovery exercises for cognitive health.</p>
            </div>
            <button class="add-exercise-btn" onclick="showAddExercise()">+ Add Exercise</button>
        </section>

        <div class="category-filters" id="categoryFilters">
            <button class="category-btn active" onclick="filterCategory('')">All</button>
            <button class="category-btn" onclick="filterCategory('acupressure')">&#x1F44C; Acupressure</button>
            <button class="category-btn" onclick="filterCategory('memory')">&#x1F9E0; Memory</button>
            <button class="category-btn" onclick="filterCategory('breathing')">&#x1F4A8; Breathing</button>
            <button class="category-btn" onclick="filterCategory('stretching')">&#x1F9D8; Stretching</button>
        </div>

        <div class="exercise-grid" id="exerciseGrid">
            <div class="no-exercises">
                <div class="icon">&#x1F9D8;</div>
                <h3>Loading exercises...</h3>
            </div>
        </div>
    </main>
</div>

<div id="addExerciseModal" class="modal-overlay">
    <div class="modal-content">
        <h2 style="margin-bottom:20px;">Add Exercise</h2>
        <form id="addExerciseForm">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" placeholder="e.g., LI4 Acupressure for Memory" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" placeholder="Describe the exercise steps, benefits, and how to perform it..."></textarea>
            </div>
            <div class="form-group">
                <label>Video URL (YouTube embed link)</label>
                <input type="url" name="video_url" placeholder="https://www.youtube.com/embed/VIDEO_ID">
                <small style="color:#64748b;">Paste the YouTube embed URL (e.g., https://www.youtube.com/embed/abc123)</small>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                <div class="form-group">
                    <label>Category</label>
                    <select name="category">
                        <option value="acupressure">Acupressure</option>
                        <option value="memory">Memory</option>
                        <option value="breathing">Breathing</option>
                        <option value="stretching">Stretching</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Duration (minutes)</label>
                    <input type="number" name="duration_minutes" value="10" min="1" max="60">
                </div>
            </div>
            <div class="form-group">
                <label>Difficulty</label>
                <select name="difficulty">
                    <option value="easy">Easy</option>
                    <option value="medium">Medium</option>
                    <option value="hard">Hard</option>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-save">Save Exercise</button>
                <button type="button" class="btn-cancel" onclick="hideAddExercise()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div id="videoModal" class="modal-overlay" onclick="closeVideoModal()">
    <div class="modal-content" style="max-width:800px;padding:20px;" onclick="event.stopPropagation()">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
            <h3 id="videoTitle">Exercise Video</h3>
            <button onclick="closeVideoModal()" style="background:none;border:none;font-size:24px;cursor:pointer;">&times;</button>
        </div>
        <div style="position:relative;padding-bottom:56.25%;height:0;">
            <iframe id="videoFrame" src="" style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;" allowfullscreen></iframe>
        </div>
        <div id="videoDescription" style="margin-top:15px;color:#64748b;line-height:1.6;"></div>
    </div>
</div>

<script>
var currentCategory = '';
var allExercises = [];

function loadExercises() {
    var url = '/SmritiMitra/api/exercises.php?action=get_exercises';
    if (currentCategory) url += '&category=' + currentCategory;

    fetch(url)
    .then(r => r.json())
    .then(data => {
        allExercises = data.exercises || [];
        renderExercises();
    })
    .catch(function() {
        document.getElementById('exerciseGrid').innerHTML = '<div class="no-exercises"><div class="icon">&#x26A0;&#xFE0F;</div><h3>Could not load exercises</h3></div>';
    });
}

function renderExercises() {
    var grid = document.getElementById('exerciseGrid');
    if (allExercises.length === 0) {
        grid.innerHTML = '<div class="no-exercises"><div class="icon">&#x1F9D8;</div><h3>No exercises found</h3><p>Add acupressure and memory exercises to get started.</p></div>';
        return;
    }

    grid.innerHTML = '';
    allExercises.forEach(function(ex) {
        var card = document.createElement('div');
        card.className = 'exercise-card';

        var videoHtml = '';
        if (ex.video_url) {
            videoHtml = '<div class="video-container"><iframe src="' + ex.video_url + '" allowfullscreen></iframe></div>';
        } else {
            videoHtml = '<div class="video-placeholder">&#x1F3AC;</div>';
        }

        var categoryIcons = { acupressure: '&#x1F44C;', memory: '&#x1F9E0;', breathing: '&#x1F4A8;', stretching: '&#x1F9D8;' };
        var difficultyColors = { easy: '#059669', medium: '#d97706', hard: '#dc2626' };

        card.innerHTML = videoHtml +
            '<div class="exercise-info">' +
                '<div class="exercise-meta">' +
                    '<span class="exercise-tag tag-category">' + (categoryIcons[ex.category] || '') + ' ' + ex.category + '</span>' +
                    '<span class="exercise-tag tag-duration">&#x23F1; ' + ex.duration_minutes + ' min</span>' +
                    '<span class="exercise-tag tag-difficulty" style="background:' + difficultyColors[ex.difficulty] + '20;color:' + difficultyColors[ex.difficulty] + ';">' + ex.difficulty + '</span>' +
                '</div>' +
                '<h3>' + ex.title + '</h3>' +
                '<p>' + (ex.description || 'No description provided.') + '</p>' +
                '<div class="exercise-actions">' +
                    (ex.video_url ? '<button class="btn-watch" onclick="watchVideo(\'' + ex.video_url + '\',\'' + ex.title.replace(/'/g,"\\'") + '\',\'' + (ex.description||'').replace(/'/g,"\\'") + '\')">&#x25B6; Watch Video</button>' : '') +
                    '<button class="btn-delete" onclick="deleteExercise(' + ex.id + ')">Delete</button>' +
                '</div>' +
            '</div>';

        grid.appendChild(card);
    });
}

function filterCategory(cat) {
    currentCategory = cat;
    document.querySelectorAll('.category-btn').forEach(function(btn) {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    loadExercises();
}

function watchVideo(url, title, desc) {
    document.getElementById('videoTitle').textContent = title;
    document.getElementById('videoFrame').src = url;
    document.getElementById('videoDescription').textContent = desc;
    document.getElementById('videoModal').style.display = 'flex';
}

function closeVideoModal() {
    document.getElementById('videoFrame').src = '';
    document.getElementById('videoModal').style.display = 'none';
}

function showAddExercise() {
    document.getElementById('addExerciseModal').style.display = 'flex';
}

function hideAddExercise() {
    document.getElementById('addExerciseModal').style.display = 'none';
    document.getElementById('addExerciseForm').reset();
}

document.getElementById('addExerciseForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var fd = new FormData(this);
    fetch('/SmritiMitra/api/exercises.php', {
        method: 'POST',
        body: new URLSearchParams({
            action: 'add_exercise',
            title: fd.get('title'),
            description: fd.get('description'),
            video_url: fd.get('video_url'),
            category: fd.get('category'),
            duration_minutes: fd.get('duration_minutes'),
            difficulty: fd.get('difficulty')
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            hideAddExercise();
            loadExercises();
        } else {
            alert('Failed to add exercise');
        }
    });
});

function deleteExercise(id) {
    if (!confirm('Delete this exercise?')) return;
    fetch('/SmritiMitra/api/exercises.php', {
        method: 'POST',
        body: new URLSearchParams({ action: 'delete_exercise', id: id })
    })
    .then(r => r.json())
    .then(() => loadExercises());
}

window.onload = function() { loadExercises(); };
</script>
</body>
</html>
