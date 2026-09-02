<?php
session_start();
include "../config/db.php";
requireLogin();
$user = getCurrentUser();
$today = date('Y-m-d');

$med_stmt = $conn->prepare("SELECT * FROM medicines WHERE user_id=? ORDER BY time_hour, time_minute");
$med_stmt->bind_param("i", $user['id']); $med_stmt->execute();
$medicines = $med_stmt->get_result();

$taken = 0; $total = 0;
$all_meds = [];
while ($row = $medicines->fetch_assoc()) { $all_meds[] = $row; if ($row['status'] === 'taken') $taken++; $total++; }

$next_med = null;
foreach ($all_meds as $m) { if ($m['status'] === 'pending') { $next_med = $m; break; } }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Medicines | SmritiMitra</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-layout">
    <?php include "../includes/sidebar.php"; ?>
    <main class="main-content">
        <?php include "../includes/header.php"; ?>

        <div class="page-heading">
            <div>
                <p class="page-label">HEALTH & WELLNESS</p>
                <h1>My Medicines 💊</h1>
                <p>Stay on track with your daily medicine schedule.</p>
            </div>
            <div>
                <button class="add-medicine-btn" onclick="showAddMedicine()">+ Add Medicine</button>
            </div>
        </div>

        <section class="medicine-summary">
            <div class="medicine-progress-card">
                <div>
                    <p class="summary-label">TODAY'S PROGRESS</p>
                    <h2><?php echo $taken . ' of ' . max($total, 1); ?></h2>
                    <p class="summary-text">Medicines completed today</p>
                </div>
                <div class="circle-progress"><?php echo $total > 0 ? round(($taken/$total)*100) : 0; ?>%</div>
            </div>
            <?php if ($next_med): ?>
            <div class="next-medicine-card">
                <div class="next-icon">💊</div>
                <div>
                    <p class="summary-label">UP NEXT</p>
                    <h3><?php echo htmlspecialchars($next_med['name']); ?></h3>
                    <p>Today at <strong><?php echo $next_med['time_hour'] . ':' . str_pad($next_med['time_minute'], 2, '0', STR_PAD_LEFT) . ' ' . $next_med['period']; ?></strong></p>
                </div>
                <span class="upcoming-badge">Upcoming</span>
            </div>
            <?php endif; ?>
        </section>

        <section class="medicine-section">
            <div class="section-title"><div><h2>Today's Schedule</h2><p>Keep track of your medicine routine.</p></div></div>
            <div class="medicine-timeline" id="medicineTimeline">
                <?php if (empty($all_meds)): ?>
                    <p style="color:#64748b;text-align:center;padding:20px;">No medicines added yet. Click "Add Medicine" to get started.</p>
                <?php else: foreach ($all_meds as $med): ?>
                <div class="medicine-item <?php echo $med['status'] === 'pending' ? 'upcoming' : ''; ?>" id="med-<?php echo $med['id']; ?>">
                    <div class="timeline-time"><strong><?php echo $med['time_hour']; ?></strong><span><?php echo $med['time_minute'] > 0 ? ':' . str_pad($med['time_minute'], 2, '0') : ''; ?> <?php echo $med['period']; ?></span></div>
                    <div class="timeline-line"></div>
                    <div class="medicine-info">
                        <div class="medicine-icon">💊</div>
                        <div><h3><?php echo htmlspecialchars($med['name']); ?></h3><p><?php echo htmlspecialchars($med['dosage']); ?></p></div>
                    </div>
                    <?php if ($med['status'] === 'taken'): ?>
                        <div class="medicine-status taken">✓ Taken</div>
                    <?php else: ?>
                        <button class="take-medicine-btn" onclick="takeMedicine(<?php echo $med['id']; ?>)">Take Medicine</button>
                    <?php endif; ?>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </section>

        <div class="health-tip">
            <div class="health-tip-icon">💡</div>
            <div><h3>Daily Health Tip</h3><p>Taking medicines at the same time every day can help build a healthy routine.</p></div>
        </div>
    </main>
</div>

<div id="addMedicineModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;justify-content:center;align-items:center;">
    <div style="background:white;border-radius:20px;padding:30px;width:90%;max-width:450px;">
        <h2 style="margin-bottom:20px;">Add Medicine</h2>
        <form id="addMedicineForm">
            <div style="margin-bottom:15px;"><label style="font-weight:600;display:block;margin-bottom:5px;">Medicine Name</label><input type="text" name="name" required style="width:100%;padding:12px;border:2px solid #e2e8f0;border-radius:10px;box-sizing:border-box;"></div>
            <div style="margin-bottom:15px;"><label style="font-weight:600;display:block;margin-bottom:5px;">Dosage</label><input type="text" name="dosage" placeholder="e.g. 500mg" style="width:100%;padding:12px;border:2px solid #e2e8f0;border-radius:10px;box-sizing:border-box;"></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:15px;">
                <div><label style="font-weight:600;display:block;margin-bottom:5px;">Time</label><input type="time" name="time" required style="width:100%;padding:12px;border:2px solid #e2e8f0;border-radius:10px;box-sizing:border-box;"></div>
                <div><label style="font-weight:600;display:block;margin-bottom:5px;">Period</label><select name="period" style="width:100%;padding:12px;border:2px solid #e2e8f0;border-radius:10px;box-sizing:border-box;"><option>AM</option><option>PM</option></select></div>
            </div>
            <div style="display:flex;gap:10px;"><button type="submit" style="flex:1;padding:12px;background:#6d5dfc;color:white;border:none;border-radius:10px;font-weight:700;cursor:pointer;">Add</button><button type="button" onclick="hideAddMedicine()" style="flex:1;padding:12px;background:#f1f5f9;border:none;border-radius:10px;font-weight:700;cursor:pointer;">Cancel</button></div>
        </form>
    </div>
</div>

<script>
function showAddMedicine() { document.getElementById('addMedicineModal').style.display='flex'; }
function hideAddMedicine() { document.getElementById('addMedicineModal').style.display='none'; }

document.getElementById('addMedicineForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    const time = fd.get('time').split(':');
    let h = parseInt(time[0]), m = parseInt(time[1]);
    const period = fd.get('period');
    if (period === 'PM' && h < 12) h += 12;
    if (period === 'AM' && h === 12) h = 0;
    let slot = 'morning'; if (h >= 12 && h < 17) slot = 'afternoon'; if (h >= 17) slot = 'evening';

    fetch('../api/medicines.php', { method:'POST', body: new URLSearchParams({ action:'add_medicine', name:fd.get('name'), dosage:fd.get('dosage'), time_slot:slot, time_hour:h, time_minute:m, period:fd.get('period') }) })
    .then(r=>r.json()).then(d => { if(d.success) location.reload(); });
});

function takeMedicine(id) {
    fetch('../api/medicines.php', { method:'POST', body: new URLSearchParams({ action:'take_medicine', medicine_id:id }) })
    .then(r=>r.json()).then(d => { if(d.success) location.reload(); });
}
</script>
</body>
</html>
