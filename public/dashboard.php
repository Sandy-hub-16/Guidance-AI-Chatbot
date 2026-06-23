<?php
require_once __DIR__ . '/../config/auth-guard.php';
require_once __DIR__ . '/../config/csrf.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token()) ?>">
    <title>Guidance Chat</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --forest: #1A3C34;
            --emerald: #2F6F62;
            --sage: #5BA896;
            --oat: #F5F0E8;
            --card: #FFFCF7;
            --terra: #D4956A;
            --terra-bg: #FBF5EC;
            --border: #E2DAC8;
            --text: #2B2825;
            --muted: #7A7367;
            --crisis: #C45B3F;
            --sidebar-w: 272px;
        }

        html,
        body {
            height: 100%;
            font-family: 'Inter', sans-serif;
            color: var(--text);
            background: var(--oat);
        }

        /* ── Layout shell ── */
        .app {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* ── Sidebar ── */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--forest);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 40;
            transform: translateX(-100%);
            transition: transform 0.28s cubic-bezier(.4, 0, .2, 1);
        }

        .sidebar.open,
        .sidebar.md-open {
            transform: translateX(0);
        }

        .sidebar-header {
            padding: 20px 18px 14px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 4px;
        }

        .sidebar-icon {
            width: 32px;
            height: 32px;
            background: rgba(255, 255, 255, 0.14);
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }

        .sidebar-title {
            font-family: 'Lexend', sans-serif;
            font-weight: 600;
            font-size: 0.95rem;
            color: white;
            letter-spacing: -0.01em;
        }

        .sidebar-student {
            font-size: 0.73rem;
            color: rgba(255, 255, 255, 0.45);
            margin-left: 41px;
        }

        .new-chat-btn {
            margin: 14px 14px 0;
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.10);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 10px;
            padding: 10px 14px;
            color: white;
            font-family: 'Lexend', sans-serif;
            font-weight: 500;
            font-size: 0.83rem;
            cursor: pointer;
            transition: background 0.15s;
            width: calc(100% - 28px);
            letter-spacing: -0.01em;
        }

        .new-chat-btn:hover {
            background: rgba(255, 255, 255, 0.16);
        }

        .new-chat-btn .plus {
            width: 20px;
            height: 20px;
            background: rgba(255, 255, 255, 0.18);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            line-height: 1;
            flex-shrink: 0;
        }

        .conv-list-label {
            font-size: 0.67rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.30);
            padding: 16px 18px 6px;
        }

        .conv-list {
            flex: 1;
            overflow-y: auto;
            padding: 0 10px 12px;
        }

        .conv-list::-webkit-scrollbar {
            width: 4px;
        }

        .conv-list::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.12);
            border-radius: 2px;
        }

        .conv-item {
            width: 100%;
            text-align: left;
            background: none;
            border: none;
            border-radius: 9px;
            padding: 9px 10px;
            cursor: pointer;
            color: rgba(255, 255, 255, 0.75);
            transition: background 0.14s, color 0.14s;
            display: block;
        }

        .conv-item:hover {
            background: rgba(255, 255, 255, 0.08);
            color: white;
        }

        .conv-item.active {
            background: rgba(255, 255, 255, 0.12);
            color: white;
        }

        .conv-preview {
            font-size: 0.83rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
        }

        .conv-date {
            font-size: 0.68rem;
            color: rgba(255, 255, 255, 0.35);
            display: block;
            margin-top: 1px;
        }

        .sidebar-footer {
            padding: 12px 18px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar-footer a {
            font-size: 0.78rem;
            color: rgba(255, 255, 255, 0.38);
            text-decoration: none;
            transition: color 0.15s;
        }

        .sidebar-footer a:hover {
            color: rgba(255, 255, 255, 0.70);
        }

        /* Sidebar overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.35);
            z-index: 39;
        }

        .sidebar-overlay.show {
            display: block;
        }

        /* ── Main content ── */
        .main {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            margin-left: 0;
            transition: margin-left 0.28s cubic-bezier(.4, 0, .2, 1);
        }

        /* ── Mobile header ── */
        .mobile-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px 16px;
            background: white;
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
        }

        .hamburger {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text);
            padding: 4px;
            border-radius: 6px;
            line-height: 1;
            font-size: 18px;
        }

        .mobile-title {
            font-family: 'Lexend', sans-serif;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--emerald);
        }

        /* ── Chat panel ── */
        .chat-panel {
            flex: 1;
            overflow-y: auto;
            padding: 28px 20px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .chat-panel::-webkit-scrollbar {
            width: 5px;
        }

        .chat-panel::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 3px;
        }

        /* Empty state */
        .empty-state {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 40px 20px;
            gap: 16px;
        }

        .empty-orb {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: radial-gradient(circle at 38% 38%, var(--sage), var(--emerald));
            box-shadow: 0 0 0 12px rgba(47, 111, 98, 0.08), 0 0 0 24px rgba(47, 111, 98, 0.04);
            animation: breathe 4s ease-in-out infinite;
        }

        @keyframes breathe {

            0%,
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 12px rgba(47, 111, 98, 0.08), 0 0 0 24px rgba(47, 111, 98, 0.04);
            }

            50% {
                transform: scale(1.06);
                box-shadow: 0 0 0 16px rgba(47, 111, 98, 0.10), 0 0 0 32px rgba(47, 111, 98, 0.05);
            }
        }

        .empty-title {
            font-family: 'Lexend', sans-serif;
            font-weight: 600;
            font-size: 1.05rem;
            color: var(--text);
        }

        .empty-sub {
            font-size: 0.85rem;
            color: var(--muted);
            max-width: 280px;
            line-height: 1.6;
        }

        /* Message rows */
        .msg-row {
            display: flex;
            opacity: 0;
            animation: msgIn 0.32s ease-out forwards;
        }

        .msg-row.student {
            justify-content: flex-end;
        }

        .msg-row.bot {
            justify-content: flex-start;
        }

        @keyframes msgIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .bubble {
            max-width: min(440px, 82%);
            border-radius: 18px;
            padding: 11px 15px;
            font-size: 0.88rem;
            line-height: 1.6;
        }

        .bubble.student {
            background: var(--emerald);
            color: white;
            border-bottom-right-radius: 5px;
        }

        .bubble.bot {
            background: white;
            border: 1.5px solid var(--border);
            color: var(--text);
            border-bottom-left-radius: 5px;
        }

        .bubble.crisis {
            border-color: var(--crisis);
            background: #FFF8F6;
        }

        .crisis-tag {
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--crisis);
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .crisis-tag::before {
            content: '';
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--crisis);
        }

        /* Recommendations */
        .recs-wrap {
            display: flex;
            justify-content: flex-start;
            margin-top: -6px;
        }

        .recs-inner {
            max-width: min(440px, 82%);
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .rec-card {
            background: var(--terra-bg);
            border-left: 3px solid var(--terra);
            border-radius: 0 10px 10px 0;
            padding: 9px 13px;
            font-size: 0.8rem;
            opacity: 0;
            animation: msgIn 0.32s ease-out forwards;
        }

        .rec-type {
            font-weight: 700;
            color: var(--terra);
            font-size: 0.72rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .rec-text {
            color: var(--text);
            line-height: 1.5;
        }

        /* Typing indicator */
        .typing-row {
            display: flex;
            justify-content: flex-start;
        }

        .typing-bubble {
            background: white;
            border: 1.5px solid var(--border);
            border-radius: 18px;
            border-bottom-left-radius: 5px;
            padding: 13px 16px;
            display: flex;
            gap: 5px;
            align-items: center;
        }

        .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--muted);
            animation: dotBounce 1.2s ease-in-out infinite;
            opacity: 0.5;
        }

        .dot:nth-child(2) {
            animation-delay: 0.15s;
        }

        .dot:nth-child(3) {
            animation-delay: 0.30s;
        }

        @keyframes dotBounce {

            0%,
            80%,
            100% {
                transform: scale(0.65);
                opacity: 0.35;
            }

            40% {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* ── Input bar ── */
        .input-bar {
            border-top: 1px solid var(--border);
            background: white;
            padding: 14px 16px;
            flex-shrink: 0;
        }

        .input-inner {
            display: flex;
            gap: 10px;
            max-width: 760px;
            margin: 0 auto;
        }

        .msg-input {
            flex: 1;
            border: 1.5px solid var(--border);
            border-radius: 12px;
            padding: 11px 16px;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            color: var(--text);
            background: var(--oat);
            outline: none;
            transition: border-color 0.18s, box-shadow 0.18s;
        }

        .msg-input:focus {
            border-color: var(--emerald);
            box-shadow: 0 0 0 3px rgba(47, 111, 98, 0.10);
            background: white;
        }

        .send-btn {
            background: var(--emerald);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 0 22px;
            font-family: 'Lexend', sans-serif;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: background 0.15s, transform 0.1s;
            flex-shrink: 0;
            letter-spacing: -0.01em;
        }

        .send-btn:hover {
            background: var(--forest);
        }

        .send-btn:active {
            transform: scale(0.97);
        }

        /* ── Check-in modal ── */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(20, 40, 35, 0.55);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 50;
            padding: 16px;
            backdrop-filter: blur(3px);
        }

        .modal-overlay.hidden {
            display: none;
        }

        .modal {
            background: var(--card);
            border-radius: 20px;
            box-shadow: 0 28px 64px rgba(20, 40, 35, 0.22), 0 4px 12px rgba(20, 40, 35, 0.10);
            width: 100%;
            max-width: 480px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 32px 30px;
        }

        .modal::-webkit-scrollbar {
            width: 4px;
        }

        .modal::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 2px;
        }

        .modal-header {
            margin-bottom: 20px;
        }

        .modal-emoji {
            font-size: 28px;
            margin-bottom: 8px;
            display: block;
        }

        .modal-title {
            font-family: 'Lexend', sans-serif;
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--text);
            letter-spacing: -0.02em;
        }

        .modal-sub {
            font-size: 0.82rem;
            color: var(--muted);
            margin-top: 4px;
            line-height: 1.5;
        }

        .ci-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-top: 18px;
        }

        .ci-grid .full {
            grid-column: 1 / -1;
        }

        .ci-field label {
            display: block;
            font-size: 0.73rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 5px;
        }

        .ci-field select,
        .ci-field input {
            width: 100%;
            border: 1.5px solid var(--border);
            border-radius: 9px;
            padding: 9px 12px;
            font-family: 'Inter', sans-serif;
            font-size: 0.85rem;
            color: var(--text);
            background: white;
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
            appearance: none;
            -webkit-appearance: none;
        }

        .ci-field select:focus,
        .ci-field input:focus {
            border-color: var(--emerald);
            box-shadow: 0 0 0 3px rgba(47, 111, 98, 0.10);
        }

        .ci-submit {
            margin-top: 22px;
            width: 100%;
            background: var(--emerald);
            color: white;
            border: none;
            border-radius: 11px;
            padding: 13px;
            font-family: 'Lexend', sans-serif;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: background 0.15s, transform 0.1s;
            box-shadow: 0 4px 16px rgba(47, 111, 98, 0.22);
            letter-spacing: -0.01em;
        }

        .ci-submit:hover {
            background: var(--forest);
        }

        .ci-submit:active {
            transform: scale(0.98);
        }

        /* ── Desktop: sidebar always visible ── */
        @media (min-width: 768px) {
            .sidebar {
                position: static;
                transform: none !important;
                flex-shrink: 0;
            }

            .mobile-header {
                display: none;
            }

            .main {
                margin-left: 0;
            }

            .sidebar-overlay {
                display: none !important;
            }

            .chat-panel {
                padding: 32px 40px;
            }

            .bubble {
                max-width: min(520px, 70%);
            }

            .recs-inner {
                max-width: min(520px, 70%);
            }
        }
    </style>
</head>

<body>

    <div class="app">

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-brand">
                    <div class="sidebar-icon">💬</div>
                    <span class="sidebar-title">Guidance Chat</span>
                </div>
                <div class="sidebar-student"><?= htmlspecialchars($_SESSION['student_name']) ?></div>
            </div>

            <button class="new-chat-btn" id="newChatBtn">
                <span class="plus">+</span>
                New conversation
            </button>

            <p class="conv-list-label">Recent</p>
            <div class="conv-list" id="conversationList"></div>

            <div class="sidebar-footer">
                <a href="logout.php">Sign out</a>
            </div>
        </aside>

        <!-- Mobile overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Main area -->
        <div class="main">

            <!-- Mobile header -->
            <header class="mobile-header">
                <button class="hamburger" id="sidebarToggle">☰</button>
                <span class="mobile-title">Guidance Chat</span>
            </header>

            <!-- Chat messages -->
            <div class="chat-panel" id="chatPanel">
                <div class="empty-state" id="emptyState">
                    <div class="empty-orb"></div>
                    <p class="empty-title">Ready when you are</p>
                    <p class="empty-sub">Share what's on your mind — academic, personal, or anything in between.</p>
                </div>
            </div>

            <!-- Input bar -->
            <div class="input-bar">
                <div class="input-inner">
                    <input id="messageInput" class="msg-input" type="text" placeholder="Type a message…">
                    <button id="sendBtn" class="send-btn">Send</button>
                </div>
            </div>

        </div>
    </div>

    <!-- Check-in modal -->
    <div id="checkinModal" class="modal-overlay hidden">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-emoji">🌿</span>
                <p class="modal-title">Quick check-in</p>
                <p class="modal-sub">Takes under a minute — helps the assistant give you better, more relevant suggestions.</p>
            </div>

            <div class="ci-grid">
                <div class="ci-field full">
                    <label>How are you feeling overall?</label>
                    <select id="ci_mood">
                        <option value="5">😊 Great</option>
                        <option value="4">🙂 Good</option>
                        <option value="3" selected>😐 Okay</option>
                        <option value="2">😔 Low</option>
                        <option value="1">😞 Really struggling</option>
                    </select>
                </div>

                <div class="ci-field">
                    <label>Sleep last night (hrs)</label>
                    <input id="ci_sleep" type="number" step="0.5" min="0" max="14" value="7">
                </div>

                <div class="ci-field">
                    <label>Study hours this week</label>
                    <input id="ci_study" type="number" step="1" min="0" value="10">
                </div>

                <div class="ci-field">
                    <label>Attendance this term (%)</label>
                    <input id="ci_attendance" type="number" step="1" min="0" max="100" value="90">
                </div>

                <div class="ci-field">
                    <label>Estimated GPA</label>
                    <input id="ci_gpa" type="number" step="0.25" min="1" max="5" value="2" placeholder="1.00 best – 5.00 lowest">
                </div>

                <div class="ci-field">
                    <label>Workload this week</label>
                    <select id="ci_workload">
                        <option value="1">Very light</option>
                        <option value="2">Light</option>
                        <option value="3" selected>Moderate</option>
                        <option value="4">Heavy</option>
                        <option value="5">Overwhelming</option>
                    </select>
                </div>

                <div class="ci-field">
                    <label>Social / groupmates</label>
                    <select id="ci_social">
                        <option value="5">Great</option>
                        <option value="4">Good</option>
                        <option value="3" selected>Okay</option>
                        <option value="2">Some friction</option>
                        <option value="1">Very strained</option>
                    </select>
                </div>

                <div class="ci-field full">
                    <label>Financial stress right now</label>
                    <select id="ci_financial">
                        <option value="1">None</option>
                        <option value="2">A little</option>
                        <option value="3" selected>Moderate</option>
                        <option value="4">High</option>
                        <option value="5">Severe</option>
                    </select>
                </div>
            </div>

            <button class="ci-submit" id="ci_submit">Start chatting →</button>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        let activeConversationId = null;

        const chatPanel = document.getElementById('chatPanel');
        const conversationList = document.getElementById('conversationList');
        const messageInput = document.getElementById('messageInput');
        const checkinModal = document.getElementById('checkinModal');
        const emptyState = document.getElementById('emptyState');

        function escapeHtml(str) {
            const d = document.createElement('div');
            d.textContent = str;
            return d.innerHTML;
        }

        const REC_LABELS = {
            self_help: 'Self-help',
            campus_resource: 'Campus resource',
            counselor_referral: 'Counselor referral',
        };

        function hideEmpty() {
            if (emptyState) emptyState.style.display = 'none';
        }

        function showEmpty() {
            if (emptyState) emptyState.style.display = '';
        }

        function renderMessage(sender, text, crisis, recommendations) {
            hideEmpty();
            const isStudent = sender === 'student';

            const row = document.createElement('div');
            row.className = `msg-row ${isStudent ? 'student' : 'bot'}`;

            const bubble = document.createElement('div');
            bubble.className = `bubble ${isStudent ? 'student' : 'bot'}${crisis ? ' crisis' : ''}`;

            let inner = '';
            if (crisis) {
                inner += `<div class="crisis-tag">Safety note</div>`;
            }
            inner += escapeHtml(text).replace(/\n/g, '<br>');
            bubble.innerHTML = inner;

            row.appendChild(bubble);
            chatPanel.appendChild(row);

            if (recommendations && recommendations.length > 0) {
                const recsWrap = document.createElement('div');
                recsWrap.className = 'recs-wrap';
                const inner2 = document.createElement('div');
                inner2.className = 'recs-inner';

                recommendations.forEach((rec, i) => {
                    const card = document.createElement('div');
                    card.className = 'rec-card';
                    card.style.animationDelay = `${200 + i * 120}ms`;
                    card.innerHTML = `
                <div class="rec-type">${REC_LABELS[rec.activity_type] || 'Suggestion'}</div>
                <div class="rec-text">${escapeHtml(rec.activity_text)}</div>`;
                    inner2.appendChild(card);
                });

                recsWrap.appendChild(inner2);
                chatPanel.appendChild(recsWrap);
            }

            chatPanel.scrollTop = chatPanel.scrollHeight;
        }

        function showTypingIndicator() {
            const row = document.createElement('div');
            row.id = 'typingIndicator';
            row.className = 'typing-row';
            row.innerHTML = `
        <div class="typing-bubble">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>`;
            chatPanel.appendChild(row);
            chatPanel.scrollTop = chatPanel.scrollHeight;
        }

        function removeTypingIndicator() {
            document.getElementById('typingIndicator')?.remove();
        }

        function clearChatPanel() {
            chatPanel.innerHTML = '';
            // Re-add empty state
            const es = document.createElement('div');
            es.id = 'emptyState';
            es.className = 'empty-state';
            es.innerHTML = `
        <div class="empty-orb"></div>
        <p class="empty-title">Ready when you are</p>
        <p class="empty-sub">Share what's on your mind — academic, personal, or anything in between.</p>`;
            chatPanel.appendChild(es);
        }

        async function loadConversations() {
            const res = await fetch('/guidance-chatbot/api/conversations.php');
            const data = await res.json();

            conversationList.innerHTML = '';

            if (data.length === 0) {
                const empty = document.createElement('p');
                empty.style.cssText = 'font-size:0.78rem;color:rgba(255,255,255,0.28);padding:10px 10px;line-height:1.5;';
                empty.textContent = 'No past conversations yet.';
                conversationList.appendChild(empty);
                return;
            }

            data.forEach(conv => {
                const item = document.createElement('button');
                item.className = 'conv-item';
                const preview = conv.preview ? escapeHtml(conv.preview).slice(0, 38) : 'Conversation';
                const date = new Date(conv.started_at).toLocaleDateString(undefined, {
                    month: 'short',
                    day: 'numeric'
                });
                item.innerHTML = `<span class="conv-preview">${preview}</span><span class="conv-date">${date}</span>`;
                item.onclick = () => openConversation(conv.conversation_id, item);
                conversationList.appendChild(item);
            });
        }

        async function openConversation(id, btn) {
            activeConversationId = id;
            clearChatPanel();

            document.querySelectorAll('.conv-item').forEach(el => el.classList.remove('active'));
            btn?.classList.add('active');

            const res = await fetch(`/guidance-chatbot/api/messages.php?conversation_id=${id}`);
            const messages = await res.json();
            messages.forEach(m => renderMessage(m.sender, m.message_text, !!m.flagged_crisis, m.recommendations));

            closeSidebar();
        }

        function openSidebar() {
            document.getElementById('sidebar').classList.add('open');
            document.getElementById('sidebarOverlay').classList.add('show');
        }

        function closeSidebar() {
            if (window.innerWidth < 768) {
                document.getElementById('sidebar').classList.remove('open');
                document.getElementById('sidebarOverlay').classList.remove('show');
            }
        }

        function startNewChat() {
            activeConversationId = null;
            clearChatPanel();
            document.querySelectorAll('.conv-item').forEach(el => el.classList.remove('active'));
            checkinModal.classList.remove('hidden');
            closeSidebar();
        }

        async function submitCheckin() {
            const payload = {
                mood_score: document.getElementById('ci_mood').value,
                sleep_hours: document.getElementById('ci_sleep').value,
                study_hours_week: document.getElementById('ci_study').value,
                attendance_pct: document.getElementById('ci_attendance').value,
                gpa_self_report: document.getElementById('ci_gpa').value,
                workload_score: document.getElementById('ci_workload').value,
                social_score: document.getElementById('ci_social').value,
                financial_stress_score: document.getElementById('ci_financial').value,
            };

            await fetch('/guidance-chatbot/api/checkin.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify(payload)
            });

            checkinModal.classList.add('hidden');
            messageInput.focus();
        }

        async function sendMessage() {
            const text = messageInput.value.trim();
            if (!text) return;

            renderMessage('student', text, false, []);
            messageInput.value = '';
            showTypingIndicator();

            try {
                const res = await fetch('/guidance-chatbot/api/chat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': csrfToken
                    },
                    body: JSON.stringify({
                        message: text,
                        conversation_id: activeConversationId
                    })
                });
                const data = await res.json();

                removeTypingIndicator();

                if (data.error) {
                    renderMessage('bot', data.error, true, []);
                    return;
                }

                const isNew = activeConversationId === null;
                activeConversationId = data.conversation_id;

                renderMessage('bot', data.reply, data.crisis, data.recommendations);

                if (isNew) loadConversations();
            } catch (e) {
                removeTypingIndicator();
                renderMessage('bot', 'Something went wrong. Please try again.', false, []);
            }
        }

        // Event bindings
        document.getElementById('newChatBtn').onclick = startNewChat;
        document.getElementById('ci_submit').onclick = submitCheckin;
        document.getElementById('sendBtn').onclick = sendMessage;
        document.getElementById('sidebarToggle')?.addEventListener('click', openSidebar);
        document.getElementById('sidebarOverlay')?.addEventListener('click', closeSidebar);
        messageInput.addEventListener('keydown', e => {
            if (e.key === 'Enter' && !e.shiftKey) sendMessage();
        });

        // Boot
        loadConversations();
        checkinModal.classList.remove('hidden');
    </script>
</body>

</html>