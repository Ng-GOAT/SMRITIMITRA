function generateReport() {
    Promise.all([
        fetch('/SmritiMitra/api/dashboard.php?action=get_dashboard').then(r => r.json()).catch(() => null),
        fetch('/SmritiMitra/api/settings.php?action=get_profile').then(r => r.json()).catch(() => null)
    ]).then(([dashData, profileData]) => {
        const user = {
            name: profileData?.profile?.full_name || document.querySelector('.patient-avatar-large, .profile-avatar-large, .user-avatar')?.textContent?.trim() || 'Patient',
            date: new Date().toLocaleDateString('en-IN', { day: 'numeric', month: 'long', year: 'numeric' })
        };
        const data = dashData || { medicines: { total: 0, taken: 0 }, games_today: 0, cognitive_score: 0, engagement_streak: 0 };
        createPDF(user, data);
    });
}

function createPDF(user, data) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'mm', 'a4');

    const pageWidth = 210;
    const margin = 20;
    const contentWidth = pageWidth - 2 * margin;
    let y = margin;

    doc.setFillColor(109, 93, 252);
    doc.rect(0, 0, pageWidth, 45, 'F');

    doc.setTextColor(255, 255, 255);
    doc.setFontSize(24);
    doc.setFont('helvetica', 'bold');
    doc.text('SmritiMitra', margin, y + 15);

    doc.setFontSize(12);
    doc.setFont('helvetica', 'normal');
    doc.text('AI-Powered Cognitive Care Report', margin, y + 23);

    doc.setFontSize(10);
    doc.text('Generated on: ' + user.date, margin, y + 30);

    y = 55;

    doc.setFillColor(245, 247, 251);
    doc.roundedRect(margin, y, contentWidth, 30, 3, 3, 'F');

    doc.setTextColor(30, 41, 59);
    doc.setFontSize(14);
    doc.setFont('helvetica', 'bold');
    doc.text('Patient Information', margin + 8, y + 10);

    doc.setFontSize(11);
    doc.setFont('helvetica', 'normal');
    doc.text('Name: ' + user.name, margin + 8, y + 20);
    doc.text('Report Date: ' + user.date, margin + 8, y + 27);

    y = 95;

    doc.setFillColor(109, 93, 252);
    doc.roundedRect(margin, y, contentWidth, 10, 2, 2, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(12);
    doc.setFont('helvetica', 'bold');
    doc.text('Daily Summary', margin + 8, y + 7);

    y = 112;

    const medTotal = data.medicines?.total || 0;
    const medTaken = data.medicines?.taken || 0;
    const gamesToday = data.games_today || 0;
    const cognitive = data.cognitive_score || 0;
    const streak = data.engagement_streak || 0;

    const summaryItems = [
        { label: 'Cognitive Score', value: cognitive + '%' },
        { label: 'Medicines Taken', value: medTaken + '/' + medTotal },
        { label: 'Games Played', value: gamesToday.toString() },
        { label: 'Engagement Streak', value: streak + ' Days' }
    ];

    const cardWidth = (contentWidth - 15) / 2;
    const cardHeight = 25;

    summaryItems.forEach((item, index) => {
        const col = index % 2;
        const row = Math.floor(index / 2);
        const x = margin + (col * (cardWidth + 8));
        const cardY = y + (row * (cardHeight + 5));

        doc.setFillColor(248, 250, 252);
        doc.roundedRect(x, cardY, cardWidth, cardHeight, 3, 3, 'F');

        doc.setTextColor(100, 116, 139);
        doc.setFontSize(9);
        doc.setFont('helvetica', 'normal');
        doc.text(item.label, x + 5, cardY + 8);

        doc.setTextColor(30, 41, 59);
        doc.setFontSize(16);
        doc.setFont('helvetica', 'bold');
        doc.text(item.value, x + 5, cardY + 18);
    });

    y = 175;

    doc.setFillColor(109, 93, 252);
    doc.roundedRect(margin, y, contentWidth, 10, 2, 2, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(12);
    doc.setFont('helvetica', 'bold');
    doc.text('Cognitive Performance', margin + 8, y + 7);

    y = 192;

    const perfItems = [
        { label: 'Memory', value: Math.min(cognitive, 100) },
        { label: 'Attention', value: Math.min(Math.round(cognitive * 1.1), 100) },
        { label: 'Recognition', value: Math.min(Math.round(cognitive * 0.9), 100) },
        { label: 'Consistency', value: Math.min(streak * 14, 100) }
    ];

    perfItems.forEach((item, index) => {
        const itemY = y + (index * 12);

        doc.setTextColor(71, 85, 105);
        doc.setFontSize(10);
        doc.setFont('helvetica', 'normal');
        doc.text(item.label, margin, itemY + 4);

        doc.setFillColor(229, 231, 235);
        doc.roundedRect(margin + 50, itemY, 100, 6, 2, 2, 'F');

        const fillWidth = (item.value / 100) * 100;
        doc.setFillColor(109, 93, 252);
        doc.roundedRect(margin + 50, itemY, fillWidth, 6, 2, 2, 'F');

        doc.setTextColor(30, 41, 59);
        doc.setFontSize(10);
        doc.setFont('helvetica', 'bold');
        doc.text(item.value + '%', margin + 155, itemY + 4);
    });

    y = 245;

    doc.setFillColor(109, 93, 252);
    doc.roundedRect(margin, y, contentWidth, 10, 2, 2, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(12);
    doc.setFont('helvetica', 'bold');
    doc.text('AI Insights & Recommendations', margin + 8, y + 7);

    y = 262;

    doc.setFillColor(245, 243, 255);
    doc.roundedRect(margin, y, contentWidth, 40, 3, 3, 'F');

    doc.setTextColor(91, 82, 199);
    doc.setFontSize(10);
    doc.setFont('helvetica', 'normal');

    let insight = '';
    if (cognitive > 70) {
        insight = 'Cognitive performance is strong. Continue regular engagement with games.';
    } else if (cognitive > 40) {
        insight = 'Performance is moderate. Try daily cognitive games for improvement.';
    } else {
        insight = 'Consider increasing cognitive activities. Memory Match recommended.';
    }

    const insightLines = doc.splitTextToSize(insight, contentWidth - 16);
    doc.text(insightLines, margin + 8, y + 10);

    doc.setTextColor(100, 116, 139);
    doc.setFontSize(9);
    doc.text('Note: This is an AI-generated insight, not a medical diagnosis.', margin + 8, y + 32);

    doc.setFillColor(245, 247, 251);
    doc.rect(0, 277, pageWidth, 20, 'F');

    doc.setTextColor(148, 163, 184);
    doc.setFontSize(8);
    doc.setFont('helvetica', 'normal');
    doc.text('SmritiMitra - AI Cognitive Care Companion | Report Generated: ' + user.date, margin, 285);
    doc.text('For informational purposes only. Not a medical diagnosis.', margin, 290);

    doc.save('SmritiMitra_Report_' + user.date.replace(/\s/g, '_') + '.pdf');
}

function generateCaregiverReport() {
    Promise.all([
        fetch('/SmritiMitra/api/dashboard.php?action=get_caregiver_data').then(r => r.json()).catch(() => null),
        fetch('/SmritiMitra/api/settings.php?action=get_profile').then(r => r.json()).catch(() => null)
    ]).then(([dashData, profileData]) => {
        if (dashData?.error) {
            const user = {
                name: profileData?.profile?.full_name || 'Patient',
                date: new Date().toLocaleDateString('en-IN', { day: 'numeric', month: 'long', year: 'numeric' })
            };
            createCaregiverPDF(user, {
                patient: { full_name: user.name },
                medicines: { total: 0, taken: 0 },
                games_today: 0,
                cognitive_score: 0,
                engagement_streak: 0,
                activities: []
            });
        } else {
            const user = {
                name: dashData?.patient?.full_name || 'Patient',
                date: new Date().toLocaleDateString('en-IN', { day: 'numeric', month: 'long', year: 'numeric' })
            };
            createCaregiverPDF(user, dashData);
        }
    });
}

function createCaregiverPDF(user, data) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'mm', 'a4');

    const pageWidth = 210;
    const margin = 20;
    const contentWidth = pageWidth - 2 * margin;
    let y = margin;

    doc.setFillColor(109, 93, 252);
    doc.rect(0, 0, pageWidth, 45, 'F');

    doc.setTextColor(255, 255, 255);
    doc.setFontSize(22);
    doc.setFont('helvetica', 'bold');
    doc.text('SmritiMitra Caregiver Report', margin, y + 15);

    doc.setFontSize(11);
    doc.setFont('helvetica', 'normal');
    doc.text('Patient Monitoring & Progress Report', margin, y + 23);
    doc.text('Generated: ' + user.date, margin, y + 30);

    y = 55;

    doc.setFillColor(245, 247, 251);
    doc.roundedRect(margin, y, contentWidth, 25, 3, 3, 'F');

    doc.setTextColor(30, 41, 59);
    doc.setFontSize(12);
    doc.setFont('helvetica', 'bold');
    doc.text('Patient: ' + (data.patient?.full_name || user.name), margin + 8, y + 10);

    doc.setFontSize(10);
    doc.setFont('helvetica', 'normal');
    doc.text('Report Period: Last 7 Days', margin + 8, y + 18);

    y = 90;

    const medTotal = data.medicines?.total || 0;
    const medTaken = data.medicines?.taken || 0;
    const games = data.games_today || 0;
    const cognitive = data.cognitive_score || 0;
    const streak = data.engagement_streak || 0;

    doc.setFillColor(109, 93, 252);
    doc.roundedRect(margin, y, contentWidth, 10, 2, 2, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(12);
    doc.setFont('helvetica', 'bold');
    doc.text('Activity Summary', margin + 8, y + 7);

    y = 108;

    const stats = [
        ['Cognitive Score', cognitive + '%'],
        ['Medicine Adherence', medTotal > 0 ? Math.round((medTaken / medTotal) * 100) + '%' : 'N/A'],
        ['Games Completed', games.toString()],
        ['Engagement Streak', streak + ' days']
    ];

    stats.forEach((stat, index) => {
        const itemY = y + (index * 10);
        doc.setTextColor(71, 85, 105);
        doc.setFontSize(10);
        doc.setFont('helvetica', 'normal');
        doc.text(stat[0] + ':', margin, itemY + 4);
        doc.setTextColor(30, 41, 59);
        doc.setFont('helvetica', 'bold');
        doc.text(stat[1], margin + 60, itemY + 4);
        doc.setFont('helvetica', 'normal');
    });

    y = 155;

    doc.setFillColor(109, 93, 252);
    doc.roundedRect(margin, y, contentWidth, 10, 2, 2, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(12);
    doc.setFont('helvetica', 'bold');
    doc.text('Caregiver Recommendations', margin + 8, y + 7);

    y = 172;

    doc.setFillColor(245, 243, 255);
    doc.roundedRect(margin, y, contentWidth, 55, 3, 3, 'F');

    doc.setTextColor(71, 85, 105);
    doc.setFontSize(10);
    doc.setFont('helvetica', 'normal');

    const recommendations = [
        '1. Encourage daily cognitive game sessions (15-20 minutes)',
        '2. Ensure all medicines are taken on schedule',
        '3. Engage in Memory Journey activities weekly',
        '4. Use AI Companion for daily conversation practice',
        '5. Monitor cognitive score trends for changes',
        '',
        'Note: This is an AI-generated report for informational',
        'purposes only. Not a medical diagnosis.'
    ];

    recommendations.forEach((line, index) => {
        doc.text(line, margin + 8, y + 8 + (index * 6));
    });

    doc.setFillColor(245, 247, 251);
    doc.rect(0, 277, pageWidth, 20, 'F');

    doc.setTextColor(148, 163, 184);
    doc.setFontSize(8);
    doc.text('SmritiMitra - AI Cognitive Care Companion | Caregiver Report', margin, 285);

    doc.save('SmritiMitra_Caregiver_Report_' + user.date.replace(/\s/g, '_') + '.pdf');
}
