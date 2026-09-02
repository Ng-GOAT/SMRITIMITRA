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
</head>
<body>
<div class="app-layout">
    <?php include "../includes/sidebar.php"; ?>
    <main class="main-content">
        <?php include "../includes/header.php"; ?>

        <section class="memory-header">
            <div>
                <p class="section-tag">PERSONAL MEMORIES</p>
                <h1>❤️ My Memory Journey</h1>
                <p>Explore and cherish the beautiful moments of your life.</p>
            </div>
            <button class="add-memory-btn" onclick="showAddMemory()">+ Add Memory</button>
        </section>

        <section class="memory-summary-grid">
            <div class="memory-summary-card"><div class="summary-icon">📸</div><div><span>Total Memories</span><strong id="totalMemories"><?php echo $total; ?></strong></div></div>
            <div class="memory-summary-card"><div class="summary-icon">❤️</div><div><span>Favorite Moments</span><strong id="favMemories"><?php echo $favs; ?></strong></div></div>
            <div class="memory-summary-card"><div class="summary-icon">👨‍👩‍👧</div><div><span>Family Memories</span><strong><?php echo $total - $favs; ?></strong></div></div>
        </section>

        <section class="memory-section">
            <div class="section-title"><div><h2>Your Life Journey</h2><p>Every memory tells a beautiful story.</p></div></div>
            <div class="memory-timeline" id="memoryTimeline">
                <?php if (empty($all_mems)): ?>
                    <p style="color:#64748b;text-align:center;padding:20px;">No memories yet. Click "Add Memory" to start preserving your beautiful moments.</p>
                <?php else: foreach ($all_mems as $mem): ?>
                <div class="timeline-memory" id="mem-<?php echo $mem['id']; ?>">
                    <div class="timeline-dot"><?php echo $mem['category'] === 'childhood' ? '👧' : ($mem['category'] === 'education' ? '🎓' : ($mem['category'] === 'family' ? '💍' : ($mem['category'] === 'celebrations' ? '🎉' : '📝'))); ?></div>
                    <div class="timeline-card">
                        <div class="timeline-year"><?php echo ucfirst($mem['category']); ?></div>
                        <h3><?php echo htmlspecialchars($mem['title']); ?></h3>
                        <p><?php echo htmlspecialchars($mem['description']); ?></p>
                        <div class="memory-tags">
                            <span onclick="toggleFav(<?php echo $mem['id']; ?>)" style="cursor:pointer;"><?php echo $mem['is_favorite'] ? '❤️ Favorite' : '🤍 Favorite'; ?></span>
                            <span onclick="deleteMemory(<?php echo $mem['id']; ?>)" style="cursor:pointer;color:#dc2626;">🗑️ Delete</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </section>
    </main>
</div>

<div id="addMemoryModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;justify-content:center;align-items:center;">
    <div style="background:white;border-radius:20px;padding:30px;width:90%;max-width:500px;">
        <h2 style="margin-bottom:20px;">Add a Memory</h2>
        <form id="addMemoryForm">
            <div style="margin-bottom:15px;"><label style="font-weight:600;display:block;margin-bottom:5px;">Title</label><input type="text" name="title" required style="width:100%;padding:12px;border:2px solid #e2e8f0;border-radius:10px;box-sizing:border-box;"></div>
            <div style="margin-bottom:15px;"><label style="font-weight:600;display:block;margin-bottom:5px;">Description</label><textarea name="description" rows="4" style="width:100%;padding:12px;border:2px solid #e2e8f0;border-radius:10px;box-sizing:border-box;resize:vertical;"></textarea></div>
            <div style="margin-bottom:15px;"><label style="font-weight:600;display:block;margin-bottom:5px;">Category</label><select name="category" style="width:100%;padding:12px;border:2px solid #e2e8f0;border-radius:10px;box-sizing:border-box;"><option value="childhood">Childhood</option><option value="education">Education</option><option value="family">Family</option><option value="celebrations">Celebrations</option><option value="other">Other</option></select></div>
            <div style="display:flex;gap:10px;"><button type="submit" style="flex:1;padding:12px;background:#6d5dfc;color:white;border:none;border-radius:10px;font-weight:700;cursor:pointer;">Save Memory</button><button type="button" onclick="hideAddMemory()" style="flex:1;padding:12px;background:#f1f5f9;border:none;border-radius:10px;font-weight:700;cursor:pointer;">Cancel</button></div>
        </form>
    </div>
</div>

<script>
function showAddMemory() { document.getElementById('addMemoryModal').style.display='flex'; }
function hideAddMemory() { document.getElementById('addMemoryModal').style.display='none'; }

document.getElementById('addMemoryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    fetch('../api/memories.php', { method:'POST', body: new URLSearchParams({ action:'add_memory', title:fd.get('title'), description:fd.get('description'), category:fd.get('category') }) })
    .then(r=>r.json()).then(d => { if(d.success) location.reload(); });
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
</script>
</body>
</html>
