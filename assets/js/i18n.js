const translations = {
    'English': {
        dashboard: 'Dashboard', games: 'Cognitive Games', memories: 'Memory Journey', medicines: 'My Medicines',
        companion: 'AI Companion', caregiver: 'Caregiver', profile: 'My Profile', settings: 'Settings',
        goodMorning: 'Good Morning', goodAfternoon: 'Good Afternoon', goodEvening: 'Good Evening',
        cognitiveActivity: 'Cognitive Activity', medicinesCompleted: 'Medicines Completed',
        activitiesCompleted: 'Activities Completed', yourCareHub: 'Your Care Hub',
        explore: 'Explore', viewSchedule: 'View Schedule', startTalking: 'Start Talking',
        viewInsights: 'View Insights', manageProfile: 'Manage Profile',
        heroTitle: 'Your mind deserves care, every day.',
        heroDesc: 'SmritiMitra is your AI-powered cognitive care companion, helping you stay engaged, remember daily routines, and stay connected with your loved ones.',
        todayProgress: "TODAY'S PROGRESS", dailyGoal: 'Daily Goal: 3 Activities',
        chooseActivity: 'Choose an Activity', selectGame: 'Select a game and enjoy your cognitive training.',
        play: 'Play', memoryMatch: 'Memory Match', memoryMatchDesc: 'Find matching pairs and strengthen your memory.',
        numberSequence: 'Number Sequence', numberSequenceDesc: 'Remember numbers and repeat the sequence.',
        patternRecog: 'Pattern Recognition', patternRecogDesc: 'Identify patterns and find what comes next.',
        objectRecog: 'Object Recognition', objectRecogDesc: 'Recognize familiar objects and everyday items.',
        aiInsight: 'AI PERFORMANCE INSIGHTS', yourPerformance: 'Your Cognitive Performance',
        addMedicine: 'Add Medicine', addMemory: 'Add Memory', saveMemory: 'Save Memory',
        medicines: 'Medicines', dailyRoutine: 'Daily Routine', memories: 'Memories', needHelp: 'Need Help?',
        typeMessage: 'Type your message...', send: 'Send', listen: 'Listen',
        takeMedicine: 'Take Medicine', taken: 'Taken', upcoming: 'Upcoming',
        totalMemories: 'Total Memories', favoriteMoments: 'Favorite Moments', familyMemories: 'Family Memories',
        personalInfo: 'Personal Information', fullName: 'Full Name', age: 'Age', dob: 'Date of Birth',
        preferredLang: 'Preferred Language', editProfile: 'Edit Profile', saveChanges: 'Save Changes',
        notifications: 'Notifications', medReminders: 'Medicine Reminders', actReminders: 'Activity Reminders',
        hydrationReminders: 'Hydration Reminders', voiceCompanion: 'Voice Companion', largeText: 'Large Text',
        voiceOutput: 'Voice Output', darkMode: 'Dark Mode', language: 'Language',
        signIn: 'Sign In', register: 'Register', signOut: 'Sign Out',
        patientOverview: 'PATIENT OVERVIEW', cognitivePerf: 'Cognitive Performance',
        medAdherence: 'Medicine Adherence', engagementStreak: 'Engagement Streak',
        todayActivities: "Today's Activities", aiInsight: 'AI INSIGHT', todaysObservation: "Today's Observation",
        remainders: 'Reminders', hydration: 'Hydration', activity: 'Activity', appointment: 'Appointment',
        reminderTitle: 'Reminder Title', reminderTime: 'Reminder Time', addReminder: 'Add Reminder',
        done: 'Done', cancel: 'Cancel', delete: 'Delete', close: 'Close',
        offlineMode: 'You are offline. Some features may be limited.'
    },
    'Hindi': {
        dashboard: 'डैशबोर्ड', games: 'संज्ञानात्मक खेल', memories: 'स्मृति यात्रा', medicines: 'मेरी दवाइयाँ',
        companion: 'AI साथी', caregiver: 'देखभाल करने वाला', profile: 'मेरी प्रोफ़ाइल', settings: 'सेटिंग्स',
        goodMorning: 'शुभ प्रभात', goodAfternoon: 'शुभ अपराह्न', goodEvening: 'शुभ सायं',
        cognitiveActivity: 'संज्ञानात्मक गतिविधि', medicinesCompleted: 'दवाइयाँ पूरी',
        activitiesCompleted: 'गतिविधियाँ पूरी', yourCareHub: 'आपका देखभाल केंद्र',
        heroTitle: 'आपके दिमाग को हर दिन देखभाल की ज़रूरत है।',
        heroDesc: 'स्मृतिमित्र आपका AI-संचालित संज्ञानात्मक देखभाल साथी है।',
        takeMedicine: 'दवाई लें', taken: 'ली गई', upcoming: 'आने वाली',
        totalMemories: 'कुल यादें', signIn: 'लॉग इन', register: 'रजिस्टर', signOut: 'लॉग आउट',
        offlineMode: 'आप ऑफ़लाइन हैं। कुछ सुविधाएँ सीमित हो सकती हैं।'
    },
    'Assamese': {
        dashboard: 'ডেচবৰ্ড', games: 'সংজ্ঞানাত্মক খেল', memories: 'স্মৃতি যাত্ৰা', medicines: 'মোৰ ওষুধ',
        companion: 'AI সঙ্গী', caregiver: 'যত্নকাৰী', profile: 'মোৰ প্ৰ\'ফাইল', settings: 'ছেটিংছ',
        goodMorning: 'শুভ পুৱা', goodAfternoon: 'শুভ দুপৰীয়া', goodEvening: 'শুভ সন্ধিয়া',
        heroTitle: 'আপোনাৰ মনক প্ৰতিদিন যত্নৰ প্ৰয়োজন।',
        signIn: 'প্ৰৱেশ কৰক', register: 'নিবন্ধন কৰক', signOut: 'লগ আউট',
        offlineMode: 'আপুনি অফলাইনত আছে।'
    },
    'Bengali': {
        dashboard: 'ড্যাশবোর্ড', games: 'জ্ঞানীয় খেলা', memories: 'স্মৃতি যাত্ৰা', medicines: 'আমার ওষুধ',
        companion: 'AI সঙ্গী', caregiver: 'পরিচালক', profile: 'আমার প্রোফাইল', settings: 'সেটিংস',
        goodMorning: 'শুভ সকাল', goodAfternoon: 'শুভ অপরাহ্ন', goodEvening: 'শুভ সন্ধ্যা',
        heroTitle: 'আপনার মনের প্রতিদিন যত্ন প্রয়োজন।',
        signIn: 'প্রবেশ করুন', register: 'নিবন্ধন করুন', signOut: 'লগ আউট',
        offlineMode: 'আপনি অফলাইনে আছেন।'
    },
    'Manipuri': {
        dashboard: 'ডেশবোর্ড', games: 'তৌরৈবা খেল', memories: 'মেমোৰী যাত্ৰা', medicines: 'ইমান অদু',
        companion: 'AI চংলী', caregiver: 'যত্ন তৌরিবা মওং', profile: 'ইমান প্ৰোফাইল', settings: 'ছেটিং',
        goodMorning: 'শুভ নুংদোক', goodAfternoon: 'শুভ ময়ৈ', goodEvening: 'শুভ অমাং',
        heroTitle: 'তমাক মনদা অদু তৌংদা যত্ন তৌবা লাগি।',
        signIn: 'পুশিল্লো', register: 'তালিকা তৌরো', signOut: 'লগ আউট',
        offlineMode: 'অফলাইন তীনা।'
    },
    'Mizo': {
        dashboard: 'Dashboard', games: 'PawnaHMnawh', memories: 'ZiakLam', medicines: 'ArsaMawh',
        companion: 'AI Pa', caregiver: 'TawhFak', profile: 'KaProfile', settings: 'Settings',
        goodMorning: 'DimNawnawh', goodAfternoon: 'DimChhung', goodEvening: 'DimZan',
        heroTitle: 'IThuAmah takah rehna tur a awm.',
        signIn: 'LoHmasa', register: 'ZiakTir', signOut: 'KhawnChho',
        offlineMode: 'I offline a ni.'
    },
    'Khasi': {
        dashboard: 'Dashboard', games: 'Phanbuh', memories: 'Jingsngew', medicines: 'Medicine',
        companion: 'AI Kynmaw', caregiver: 'Kynmawbor', profile: 'Profile', settings: 'Settings',
        goodMorning: 'Sawurphew', goodAfternoon: 'Sawurphyllar', goodEvening: 'Sawurpynjem',
        heroTitle: 'Kynmaw da ka synshor.',
        signIn: 'Shong', register: 'Khraw', signOut: 'Pyllait',
        offlineMode: 'Online long wet.'
    }
};

let currentLanguage = localStorage.getItem('preferredLanguage') || 'English';

function setLanguage(lang) {
    currentLanguage = lang;
    localStorage.setItem('preferredLanguage', lang);
    applyLanguage(lang);
}

function t(key) {
    return (translations[currentLanguage] && translations[currentLanguage][key]) ||
           (translations['English'] && translations['English'][key]) ||
           key;
}

function applyLanguage(lang) {
    currentLanguage = lang;
    document.querySelectorAll('[data-i18n]').forEach(el => {
        const key = el.getAttribute('data-i18n');
        if (translations[lang] && translations[lang][key]) {
            el.textContent = translations[lang][key];
        }
    });
    document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
        const key = el.getAttribute('data-i18n-placeholder');
        if (translations[lang] && translations[lang][key]) {
            el.placeholder = translations[lang][key];
        }
    });
}

window.i18nApply = applyLanguage;
window.i18nSetLanguage = setLanguage;
window.i18nT = t;

document.addEventListener('DOMContentLoaded', () => {
    applyLanguage(currentLanguage);
});
