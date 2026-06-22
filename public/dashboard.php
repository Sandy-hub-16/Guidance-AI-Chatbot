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
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Inter', sans-serif; background: #FAF8F3; }
  .font-display { font-family: 'Lexend', sans-serif; }
  ::-webkit-scrollbar { width: 6px; }
  ::-webkit-scrollbar-thumb { background: #D8D0C0; border-radius: 3px; }

  @keyframes messageIn {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
  }
  .msg-enter { opacity: 0; animation: messageIn 0.35s ease-out forwards; }

  @keyframes typingDot {
    0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
    40%           { transform: scale(1);   opacity: 1; }
  }
  .typing-dot { animation: typingDot 1.2s infinite ease-in-out; }
  .typing-dot:nth-child(2) { animation-delay: 0.15s; }
  .typing-dot:nth-child(3) { animation-delay: 0.3s; }
</style>
</head>
<body class="h-screen flex overflow-hidden text-[#2B2825]">

<!-- Sidebar -->
<aside id="sidebar" class="w-72 bg-[#F0EBE0] flex flex-col shrink-0 border-r border-[#E3DBC9] -translate-x-full md:translate-x-0 fixed md:static inset-y-0 left-0 z-30 transition-transform">
    <div class="p-4 border-b border-[#E3DBC9]">
        <h1 class="font-display font-semibold text-[#2F6F62]">Guidance Chat</h1>
        <p class="text-xs text-[#7A7367] mt-0.5"><?= htmlspecialchars($_SESSION['student_name']) ?></p>
    </div>
    <div class="p-3">
        <button id="newChatBtn" class="w-full bg-[#2F6F62] hover:bg-[#25564B] text-white text-sm font-medium rounded-lg py-2.5 transition-colors">
            + New chat
        </button>
    </div>
    <div id="conversationList" class="flex-1 overflow-y-auto px-2 space-y-1 pb-3"></div>
    <div class="p-3 border-t border-[#E3DBC9]">
        <a href="logout.php" class="text-xs text-[#7A7367] hover:text-[#2B2825]">Log out</a>
    </div>
</aside>

<!-- Main -->
<div class="flex-1 flex flex-col min-w-0">
    <header class="md:hidden flex items-center gap-3 p-3 border-b border-[#E3DBC9] bg-white">
        <button id="sidebarToggle" class="text-[#2B2825]">☰</button>
        <span class="font-display font-medium text-sm">Guidance Chat</span>
    </header>

    <div id="chatPanel" class="flex-1 overflow-y-auto px-4 md:px-10 py-6 space-y-4"></div>

    <div class="border-t border-[#E3DBC9] bg-white p-3 md:p-4">
        <div class="flex gap-2 max-w-3xl mx-auto">
            <input id="messageInput" type="text" placeholder="Type your message..."
                   class="flex-1 border border-[#E3DBC9] rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#2F6F62]">
            <button id="sendBtn" class="bg-[#2F6F62] hover:bg-[#25564B] text-white px-5 rounded-lg text-sm font-medium transition-colors">
                Send
            </button>
        </div>
    </div>
</div>

<!-- Check-in modal -->
<div id="checkinModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-md w-full p-6 max-h-[90vh] overflow-y-auto">
        <h2 class="font-display font-semibold text-lg">Quick check-in</h2>
        <p class="text-sm text-[#7A7367] mt-1 mb-4">This helps the assistant give you better suggestions. Takes under a minute.</p>

        <div class="space-y-3 text-sm">
            <div>
                <label class="block font-medium mb-1">How are you feeling overall?</label>
                <select id="ci_mood" class="w-full border border-[#E3DBC9] rounded-lg px-3 py-2">
                    <option value="5">Great</option>
                    <option value="4">Good</option>
                    <option value="3" selected>Okay</option>
                    <option value="2">Low</option>
                    <option value="1">Really struggling</option>
                </select>
            </div>
            <div>
                <label class="block font-medium mb-1">Hours of sleep last night</label>
                <input id="ci_sleep" type="number" step="0.5" min="0" max="14" value="7"
                       class="w-full border border-[#E3DBC9] rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block font-medium mb-1">Study hours this past week</label>
                <input id="ci_study" type="number" step="1" min="0" value="10"
                       class="w-full border border-[#E3DBC9] rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block font-medium mb-1">Attendance this term (%)</label>
                <input id="ci_attendance" type="number" step="1" min="0" max="100" value="90"
                       class="w-full border border-[#E3DBC9] rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block font-medium mb-1">Estimated GPA (1.00 best – 5.00 lowest)</label>
                <input id="ci_gpa" type="number" step="0.25" min="1" max="5" value="2"
                       class="w-full border border-[#E3DBC9] rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block font-medium mb-1">How heavy is your workload this week?</label>
                <select id="ci_workload" class="w-full border border-[#E3DBC9] rounded-lg px-3 py-2">
                    <option value="1">Very light</option>
                    <option value="2">Light</option>
                    <option value="3" selected>Moderate</option>
                    <option value="4">Heavy</option>
                    <option value="5">Overwhelming</option>
                </select>
            </div>
            <div>
                <label class="block font-medium mb-1">How are things with groupmates/friends?</label>
                <select id="ci_social" class="w-full border border-[#E3DBC9] rounded-lg px-3 py-2">
                    <option value="5">Great</option>
                    <option value="4">Good</option>
                    <option value="3" selected>Okay</option>
                    <option value="2">Some friction</option>
                    <option value="1">Very strained</option>
                </select>
            </div>
            <div>
                <label class="block font-medium mb-1">Financial stress right now</label>
                <select id="ci_financial" class="w-full border border-[#E3DBC9] rounded-lg px-3 py-2">
                    <option value="1">None</option>
                    <option value="2">A little</option>
                    <option value="3" selected>Moderate</option>
                    <option value="4">High</option>
                    <option value="5">Severe</option>
                </select>
            </div>
        </div>

        <button id="ci_submit" class="w-full bg-[#2F6F62] hover:bg-[#25564B] text-white rounded-lg py-2.5 text-sm font-medium mt-5">
            Start chatting
        </button>
    </div>
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
let activeConversationId = null;

const chatPanel        = document.getElementById('chatPanel');
const conversationList  = document.getElementById('conversationList');
const messageInput      = document.getElementById('messageInput');
const checkinModal      = document.getElementById('checkinModal');

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function recBadgeLabel(type) {
    return { self_help: 'Self-help', campus_resource: 'Campus resource', counselor_referral: 'Counselor referral' }[type] || 'Suggestion';
}

function renderMessage(sender, text, crisis, recommendations) {
    const isStudent = sender === 'student';
    const wrapper = document.createElement('div');
    wrapper.className = `flex msg-enter ${isStudent ? 'justify-end' : 'justify-start'}`;

    const bubbleColor = isStudent
        ? 'bg-[#2F6F62] text-white'
        : crisis
            ? 'bg-white border-2 border-[#C45B3F] text-[#2B2825]'
            : 'bg-white border border-[#E3DBC9] text-[#2B2825]';

    let html = `<div class="max-w-md md:max-w-lg rounded-2xl px-4 py-2.5 text-sm ${bubbleColor}">`;
    if (crisis) html += `<p class="text-xs font-semibold text-[#C45B3F] mb-1">Safety note</p>`;
    html += escapeHtml(text).replace(/\n/g, '<br>');
    html += '</div>';
    wrapper.innerHTML = html;

    chatPanel.appendChild(wrapper);

    if (recommendations && recommendations.length > 0) {
        const recWrap = document.createElement('div');
        recWrap.className = 'flex justify-start';
        const inner = document.createElement('div');
        inner.className = 'max-w-md md:max-w-lg space-y-1.5 mt-1';

        recommendations.forEach((rec, i) => {
            const card = document.createElement('div');
            card.className = 'msg-enter border-l-4 border-[#B8935A] bg-[#FBF6EC] rounded-r-lg px-3 py-2 text-xs';
            card.style.animationDelay = `${250 + i * 150}ms`;
            card.innerHTML = `
                <span class="font-semibold text-[#8A6A35]">${recBadgeLabel(rec.activity_type)}</span>
                <p class="mt-0.5 text-[#2B2825]">${escapeHtml(rec.activity_text)}</p>`;
            inner.appendChild(card);
        });

        recWrap.appendChild(inner);
        chatPanel.appendChild(recWrap);
    }

    chatPanel.scrollTop = chatPanel.scrollHeight;
}

function showTypingIndicator() {
    const wrapper = document.createElement('div');
    wrapper.id = 'typingIndicator';
    wrapper.className = 'flex justify-start msg-enter';
    wrapper.innerHTML = `
        <div class="bg-white border border-[#E3DBC9] rounded-2xl px-4 py-3 flex gap-1.5">
            <span class="typing-dot w-1.5 h-1.5 bg-[#7A7367] rounded-full"></span>
            <span class="typing-dot w-1.5 h-1.5 bg-[#7A7367] rounded-full"></span>
            <span class="typing-dot w-1.5 h-1.5 bg-[#7A7367] rounded-full"></span>
        </div>`;
    chatPanel.appendChild(wrapper);
    chatPanel.scrollTop = chatPanel.scrollHeight;
}

function removeTypingIndicator() {
    document.getElementById('typingIndicator')?.remove();
}

function clearChatPanel() {
    chatPanel.innerHTML = '';
}

async function loadConversations() {
    const res = await fetch('/guidance-chatbot/api/conversations.php');
    const data = await res.json();

    conversationList.innerHTML = '';
    data.forEach(conv => {
        const item = document.createElement('button');
        item.className = 'w-full text-left px-3 py-2 rounded-lg hover:bg-[#E3DBC9] text-sm text-[#2B2825] transition-colors';
        const preview = conv.preview ? escapeHtml(conv.preview).slice(0, 40) : 'New conversation';
        const date = new Date(conv.started_at).toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
        item.innerHTML = `<p class="truncate">${preview}</p><p class="text-xs text-[#7A7367]">${date}</p>`;
        item.onclick = () => openConversation(conv.conversation_id);
        conversationList.appendChild(item);
    });
}

async function openConversation(id) {
    activeConversationId = id;
    clearChatPanel();
    const res = await fetch(`/guidance-chatbot/api/messages.php?conversation_id=${id}`);
    const messages = await res.json();
    messages.forEach(m => renderMessage(m.sender, m.message_text, !!m.flagged_crisis, m.recommendations));
    if (window.innerWidth < 768) document.getElementById('sidebar').classList.add('-translate-x-full');
}

function startNewChat() {
    activeConversationId = null;
    clearChatPanel();
    checkinModal.classList.remove('hidden');
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
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
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

    const res = await fetch('/guidance-chatbot/api/chat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
        body: JSON.stringify({ message: text, conversation_id: activeConversationId })
    });
    const data = await res.json();

    removeTypingIndicator();

    if (data.error) {
        renderMessage('bot', data.error, true, []);
        return;
    }

    const isNewConversation = activeConversationId === null;
    activeConversationId = data.conversation_id;

    renderMessage('bot', data.reply, data.crisis, data.recommendations);

    if (isNewConversation) loadConversations();
}

document.getElementById('newChatBtn').onclick = startNewChat;
document.getElementById('ci_submit').onclick = submitCheckin;
document.getElementById('sendBtn').onclick = sendMessage;
messageInput.addEventListener('keydown', e => { if (e.key === 'Enter') sendMessage(); });
document.getElementById('sidebarToggle')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('-translate-x-full');
});

// On load: show check-in modal for a fresh session, load past conversations in background
loadConversations();
checkinModal.classList.remove('hidden');
</script>

</body>
</html>