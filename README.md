# 🧠 SmritiMitra

## AI-Powered Cognitive Care Companion

SmritiMitra is a cognitive care platform designed to support individuals with memory and cognitive challenges in managing their daily routines, engaging in cognitive activities, preserving memories, and staying connected with caregivers.

The platform provides a simple, accessible, and user-friendly digital environment where users can access cognitive games, medicine reminders, memory activities, an AI companion, and caregiver insights.

---

## ✨ Features

### 🔐 Authentication & User Management
- Patient and Caregiver registration
- Secure login with password hashing (bcrypt)
- Session-based authentication
- Role-based access (patient/caregiver)

---

### 🏠 Smart Dashboard
A centralized dashboard providing real-time overview of:
- Cognitive activity score (from game performance)
- Medicine completion status
- Daily activities completed
- Quick access to all features

---

### 🎮 Cognitive Games (with Adaptive AI)

Interactive cognitive activities with **real-time score tracking**:

- **Memory Match** — Find matching pairs and strengthen memory
- **Number Sequence** — Remember numbers and repeat the sequence
- **Pattern Recognition** — Identify patterns and find what comes next
- **Routine Challenge** — Order daily activities in correct sequence

**AI Features:**
- Performance tracking and analytics
- Adaptive difficulty based on player accuracy
- AI-generated cognitive insights
- Progress saved to database

---

### ❤️ Memory Journey

A personalized memory space where users can:
- **Add** new memories with title, description, and category
- **View** memories in a beautiful timeline
- **Favorite** special memories
- **Delete** memories when needed
- Categories: Childhood, Education, Family, Celebrations, Other

---

### 💊 Medicine Management

Full-featured medicine management:
- **Add** medicines with name, dosage, and time
- **Track** medicine status (taken/pending)
- **Take** medicine with one click
- **Visual timeline** of daily medicine schedule
- **Progress tracking** with circular progress indicator
- **Browser notifications** for medicine reminders

---

### 🎙️ AI Companion (Multilingual + Voice)

Interactive AI companion with:
- **Text-to-Speech** voice output in multiple languages
- **Speech-to-Text** voice input (browser API)
- **NER regional language** support (Assamese, Bengali, Manipuri, etc.)
- **Contextual responses** for medicines, routines, memories, exercises, hydration
- **Suggested questions** for quick interaction
- **Voice output toggle** in settings

---

### 👨‍👩‍👧 Caregiver Dashboard

Real-time monitoring dashboard:
- **Patient overview** with activity status
- **Medicine adherence** tracking
- **Cognitive performance** analytics
- **Engagement streak** tracking
- **Activity log** with completion status
- **AI-generated observations** and insights
- **Performance metrics** (Memory, Attention, Recognition, Consistency)

---

### 👤 User Profile

Full profile management:
- Edit personal information (name, age, DOB)
- Preferred language selection
- Care preferences display
- Caregiver connection option
- Privacy & security settings

---

### ⚙️ Settings (Persistent)

App-style settings with **database persistence**:
- 🔔 Medicine reminders toggle
- 🔔 Activity reminders toggle
- 💧 Hydration reminders toggle
- 🎙️ Voice companion toggle
- 🔊 Voice output toggle (Text-to-Speech)
- 🔎 Large text accessibility toggle
- 🌙 Dark mode toggle
- 🌐 Language selection (12 languages)
- 🔐 Privacy & security
- 🚪 Sign out

---

### 🌐 Multilingual Support (NER Languages)

Supports 12 languages including North-Eastern Region languages:
- English, Hindi, Marathi
- **Assamese, Bengali, Manipuri**
- **Mizo, Nagamese, Khasi**
- **Garo, Bodo, Tripuri**

UI elements and AI companion responses adapt to selected language.

---

### 🔔 Notification System

Browser-based notification system:
- Medicine reminders
- Hydration reminders
- Activity reminders
- Automatic permission request
- Background checking every minute

---

### 📱 Offline Support

Works in low-connectivity environments:
- **Service Worker** for caching
- **localStorage** for settings persistence
- **Offline data sync** when connection returns
- **Offline mode indicator** banner
- Cached pages for offline browsing

---

### 🎨 User Interface

Accessible and elderly-friendly interface:
- Professional dashboard layout
- Collapsible sidebar
- Active navigation highlighting
- Responsive design (mobile/tablet/desktop)
- Smooth hover effects
- Accessibility-focused controls
- Large readable cards
- Dark mode support
- Large text mode

---

## 🛠️ Technologies Used

### Frontend
- HTML5
- CSS3
- JavaScript (Vanilla)
- Web Speech API (Voice Recognition + Text-to-Speech)
- Service Workers (Offline Support)

### Backend
- PHP
- MySQLi (Prepared Statements)

### Database
- MySQL

### APIs
- Notification API (Browser Notifications)
- Speech Recognition API
- Speech Synthesis API

---

## 📁 Project Structure

```
SmritiMitra/
│
├── assets/
│   ├── css/
│   │   └── style.css
│   │
│   ├── js/
│   │   ├── sidebar.js
│   │   ├── settings.js
│   │   ├── companion.js
│   │   ├── memory-match.js
│   │   ├── number-sequence.js
│   │   ├── pattern-recognition.js
│   │   ├── routine-challenge.js
│   │   ├── notifications.js
│   │   ├── offline.js
│   │   ├── i18n.js
│   │   └── sw.js (Service Worker)
│   │
│   └── manifest.json
│
├── config/
│   └── db.php
│
├── database/
│   └── schema.sql
│
├── api/
│   ├── medicines.php
│   ├── memories.php
│   ├── games.php
│   ├── settings.php
│   ├── reminders.php
│   └── dashboard.php
│
├── includes/
│   ├── sidebar.php
│   └── header.php
│
├── pages/
│   ├── login.php
│   ├── register.php
│   ├── logout.php
│   ├── games.php
│   ├── memory-match.php
│   ├── number-sequence.php
│   ├── pattern-recognition.php
│   ├── routine-challenge.php
│   ├── memories.php
│   ├── medicines.php
│   ├── companion.php
│   ├── caregiver.php
│   ├── profile.php
│   └── settings.php
│
├── index.php
│
└── README.md
```

---

## 🚀 Setup Instructions

### Prerequisites
- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx web server

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-repo/SmritiMitra.git
   ```

2. **Create the database**
   - Import `database/schema.sql` into MySQL
   - Or run the SQL commands manually

3. **Configure database connection**
   - Edit `config/db.php` with your database credentials

4. **Set up web server**
   - Point document root to the SmritiMitra folder
   - Ensure PHP has read/write permissions

5. **Access the application**
   - Open browser and navigate to `http://localhost/SmritiMitra/`
   - Register a new account
   - Start using SmritiMitra!

---

## 🎯 Problem Statement Coverage

| Requirement | Status |
|---|---|
| Interactive cognitive games | ✅ 4 working games |
| Memory improvement | ✅ Memory Match |
| Attention & concentration | ✅ Number Sequence |
| Daily routine recall | ✅ Routine Challenge |
| Pattern & object recognition | ✅ Pattern Recognition |
| AI/ML adaptive difficulty | ✅ Based on accuracy |
| Multilingual support | ✅ 12 languages |
| Voice-assisted interaction | ✅ Speech-to-Text + Text-to-Speech |
| Culturally familiar themes | ✅ NER language support |
| Medicine reminders | ✅ Browser notifications |
| Hydration reminders | ✅ Browser notifications |
| Daily activity reminders | ✅ Browser notifications |
| Appointment reminders | ✅ Reminder system |
| Caregiver monitoring dashboard | ✅ Real-time data |
| Low-connectivity/offline support | ✅ Service Worker + localStorage |
| Mobile/tablet accessible | ✅ Responsive design |
| Elderly-friendly interface | ✅ Large text, dark mode |
| Cognitive performance tracking | ✅ Database + analytics |
| Secure patient data | ✅ Password hashing + sessions |

---

## ⚠️ Disclaimer

SmritiMitra is a **cognitive care support platform** and is not intended to provide:
- Medical diagnosis
- Medical treatment
- Emergency medical advice

Any AI-generated observations or insights are provided for informational purposes only and should not replace advice from qualified healthcare professionals.

---

## 👩‍💻 Developed By

### Team SmritiMitra

Developed as an innovative **AI-assisted cognitive care and healthcare solution**.

---

## 🌟 Vision

> **Making cognitive care more accessible, engaging, personalized, and connected.**

We envision SmritiMitra as a digital companion that can help individuals and their caregivers manage daily cognitive care through technology, intelligent assistance, and meaningful human connections.
