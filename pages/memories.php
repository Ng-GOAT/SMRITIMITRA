<?php
session_start();
include "../config/db.php";
requireLogin();
$user = getCurrentUser();

$mem_stmt = $conn->prepare("SELECT * FROM memories WHERE user_id=? ORDER BY created_at DESC");
$mem_stmt->bind_param("i", $user['id']); $mem_stmt->execute();
$memories = $mem_stmt->get_result();

$total = 0; $favs = 0; $all_mems = [];
while ($row = $memories->fetch_assoc()) { $all_mems[] = $row; $total++; if ($row['is_favorite']) $favs++; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memory Journey | SmritiMitra</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .photo-upload-area {
            border: 2px dashed #c7d2fe;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8fafc;
            position: relative;
        }
        .photo-upload-area:hover { border-color: #6d5dfc; background: #f0f0ff; }
        .photo-upload-area.has-photo { padding: 10px; }
        .photo-upload-area input[type="file"] {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            opacity: 0; cursor: pointer;
        }
        .photo-preview {
            max-width: 100%;
            max-height: 200px;
            border-radius: 10px;
            object-fit: cover;
        }
        .photo-placeholder { color: #94a3b8; }
        .photo-placeholder .icon { font-size: 36px; margin-bottom: 8px; }
        .timeline-photo {
            width: 100%;
            border-radius: 12px;
            margin-bottom: 12px;
            max-height: 250px;
            object-fit: cover;
            cursor: pointer;
        }
        .photo-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.85);
            z-index: 10000;
            justify-content: center;
            align-items: center;
            cursor: pointer;
        }
        .photo-overlay.active { display: flex; }
        .photo-overlay img {
            max-width: 90%;
            max-height: 90%;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        }
    </style>
</head>
<body>
<div class="app-layout">
    <?php include "../includes/sidebar.php"; ?>
    <main class="main-content">
        <?php include "../includes/header.php"; ?>

        <section class="memory-header">
            <div>
                <p class="section-tag">PERSONAL MEMORIES</p>
                <h1>&#x1F495; My Memory Journey</h1>
                <p>Explore and cherish the beautiful moments of your life.</p>
            </div>
            <button class="add-memory-btn" onclick="showAddMemory()">+ Add Memory</button>
        </section>

        <section class="memory-summary-grid">
            <div class="memory-summary-card"><div class="summary-icon">&#x1F4F8;</div><div><span>Total Memories</span><strong id="totalMemories"><?php echo $total; ?></strong></div></div>
            <div class="memory-summary-card"><div class="summary-icon">&#x1F495;</div><div><span>Favorite Moments</span><strong id="favMemories"><?php echo $favs; ?></strong></div></div>
            <div class="memory-summary-card"><div class="summary-icon">&#x1F468;&#x200D;&#x1F469;&#x200D;&#x1F467;</div><div><span>Family Memories</span><strong><?php echo $total - $favs; ?></strong></div></div>
        </section>

        <section class="memory-section">
            <div class="section-title"><div><h2>Your Life Journey</h2><p>Every memory tells a beautiful story.</p></div></div>
            <div class="memory-timeline" id="memoryTimeline">
                <?php if (empty($all_mems)): ?>
                    <p style="color:#64748b;text-align:center;padding:20px;">No memories yet. Click "Add Memory" to start preserving your beautiful moments.</p>
                <?php else: foreach ($all_mems as $mem): ?>
                <div class="timeline-memory" id="mem-<?php echo $mem['id']; ?>">
                    <div class="timeline-dot"><?php echo $mem['category'] === 'childhood' ? '&#x1F467;' : ($mem['category'] === 'education' ? '&#x1F393;' : ($mem['category'] === 'family' ? '&#x1F491;' : ($mem['category'] === 'celebrations' ? '&#x1F389;' : '&#x1F4DD;'))); ?></div>
                    <div class="timeline-card">
                        <div class="timeline-year"><?php echo ucfirst($mem['category']); ?></div>
                        <?php if (!empty($mem['photo_url'])): ?>
                            <img src="<?php echo htmlspecialchars($mem['photo_url']); ?>" alt="Memory photo" class="timeline-photo" onclick="viewPhoto('<?php echo htmlspecialchars($mem['photo_url']); ?>')">
                        <?php endif; ?>
                        <h3><?php echo htmlspecialchars($mem['title']); ?></h3>
                        <p><?php echo htmlspecialchars($mem['description']); ?></p>
                        <div class="memory-tags">
                            <span onclick="toggleFav(<?php echo $mem['id']; ?>)" style="cursor:pointer;"><?php echo $mem['is_favorite'] ? '&#x1F495; Favorite' : '&#x1F90D; Favorite'; ?></span>
                            <span onclick="deleteMemory(<?php echo $mem['id']; ?>)" style="cursor:pointer;color:#dc2626;">&#x1F5D1;&#xFE0F; Delete</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </section>
    </main>
</div>

<div id="addMemoryModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;justify-content:center;align-items:center;">
    <div style="background:white;border-radius:20px;padding:30px;width:90%;max-width:500px;max-height:90vh;overflow-y:auto;">
        <h2 style="margin-bottom:20px;">Add a Memory</h2>
        <form id="addMemoryForm" enctype="multipart/form-data">
            <div style="margin-bottom:15px;">
                <label style="font-weight:600;display:block;margin-bottom:5px;">Photo (optional)</label>
                <div class="photo-upload-area" id="photoUploadArea">
                    <input type="file" name="photo" id="photoInput" accept="image/*" onchange="previewPhoto(this)">
                    <div class="photo-placeholder" id="photoPlaceholder">
                        <div class="icon">&#x1F4F7;</div>
                        <p>Click to upload a photo</p>
                        <p style="font-size:12px;">JPG, PNG, GIF or WebP (max 5MB)</p>
                    </div>
                    <img id="photoPreview" class="photo-preview" style="display:none;">
                </div>
            </div>
            <div style="margin-bottom:15px;"><label style="font-weight:600;display:block;margin-bottom:5px;">Title</label><input type="text" name="title" required style="width:100%;padding:12px;border:2px solid #e2e8f0;border-radius:10px;box-sizing:border-box;"></div>
            <div style="margin-bottom:15px;"><label style="font-weight:600;display:block;margin-bottom:5px;">Description</label><textarea name="description" rows="4" style="width:100%;padding:12px;border:2px solid #e2e8f0;border-radius:10px;box-sizing:border-box;resize:vertical;"></textarea></div>
            <div style="margin-bottom:15px;"><label style="font-weight:600;display:block;margin-bottom:5px;">Category</label><select name="category" style="width:100%;padding:12px;border:2px solid #e2e8f0;border-radius:10px;box-sizing:border-box;"><option value="childhood">Childhood</option><option value="education">Education</option><option value="family">Family</option><option value="celebrations">Celebrations</option><option value="other">Other</option></select></div>
            <div style="display:flex;gap:10px;"><button type="submit" style="flex:1;padding:12px;background:#6d5dfc;color:white;border:none;border-radius:10px;font-weight:700;cursor:pointer;">Save Memory</button><button type="button" onclick="hideAddMemory()" style="flex:1;padding:12px;background:#f1f5f9;border:none;border-radius:10px;font-weight:700;cursor:pointer;">Cancel</button></div>
        </form>
    </div>
</div>

<div id="photoOverlay" class="photo-overlay" onclick="this.classList.remove('active')">
    <img id="overlayImage" src="">
</div>

<script>
function showAddMemory() {
    document.getElementById('addMemoryModal').style.display='flex';
    document.getElementById('addMemoryForm').reset();
    document.getElementById('photoPreview').style.display='none';
    document.getElementById('photoPlaceholder').style.display='block';
    document.getElementById('photoUploadArea').classList.remove('has-photo');
}
function hideAddMemory() { document.getElementById('addMemoryModal').style.display='none'; }

function previewPhoto(input) {
    if (input.files && input.files[0]) {
        if (input.files[0].size > 5*1024*1024) {
            alert('Photo must be under 5MB');
            input.value = '';
            return;
        }
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('photoPreview').src = e.target.result;
            document.getElementById('photoPreview').style.display = 'block';
            document.getElementById('photoPlaceholder').style.display = 'none';
            document.getElementById('photoUploadArea').classList.add('has-photo');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

document.getElementById('addMemoryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var fd = new FormData(this);
    fd.append('action', 'add_memory');
    fetch('../api/memories.php', { method:'POST', body: fd })
    .then(r=>r.json()).then(d => { if(d.success) location.reload(); else alert('Failed to save memory.'); });
});

function toggleFav(id) {
    fetch('../api/memories.php', { method:'POST', body: new URLSearchParams({ action:'toggle_favorite', memory_id:id }) })
    .then(r=>r.json()).then(() => location.reload());
}

function deleteMemory(id) {
    if (confirm('Delete this memory?')) {
        fetch('../api/memories.php', { method:'POST', body: new URLSearchParams({ action:'delete_memory', memory_id:id }) })
        .then(r=>r.json()).then(() => location.reload());
    }
}

function viewPhoto(src) {
    document.getElementById('overlayImage').src = src;
    document.getElementById('photoOverlay').classList.add('active');
}
</script>
</body>
</html>
