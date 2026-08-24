<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel AI Agent Tool Suite & Learning Playground</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #090d16;
            --surface: #111827;
            --surface-hover: #1f2937;
            --border: #1e293b;
            --border-highlight: #334155;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --accent-purple: #8b5cf6;
            --accent-indigo: #6366f1;
            --accent-cyan: #06b6d4;
            --accent-emerald: #10b981;
            --accent-rose: #f43f5e;
            --accent-amber: #f59e0b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg);
            color: var(--text-primary);
            font-family: 'Plus Jakarta Sans', sans-serif;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-bottom: 60px;
        }

        code, pre {
            font-family: 'Fira Code', monospace;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px 20px;
            width: 100%;
        }

        /* Top Header */
        .hero {
            text-align: center;
            padding: 40px 0 30px;
            position: relative;
        }

        .badge-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 600;
            background: rgba(99, 102, 241, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.3);
            color: #a5b4fc;
            margin-bottom: 16px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .hero h1 {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #ffffff 0%, #cbd5e1 50%, #818cf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }

        .hero p {
            color: var(--text-secondary);
            font-size: 1.1rem;
            max-width: 720px;
            margin: 0 auto;
        }

        /* 4 Core Tools Overview Cards */
        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
            margin: 30px 0;
        }

        .tool-pill {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 20px;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .tool-pill:hover {
            border-color: var(--border-highlight);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4);
        }

        .tool-pill .icon {
            font-size: 1.6rem;
            margin-bottom: 10px;
            display: inline-block;
        }

        .tool-pill h3 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tool-pill p {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .tool-method {
            font-size: 0.7rem;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
            background: rgba(16, 185, 129, 0.2);
            color: var(--accent-emerald);
        }

        /* Section Layout */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 30px;
        }

        @media (max-width: 900px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
        }

        .panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
            display: flex;
            flex-direction: column;
        }

        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--border);
        }

        .panel-title {
            font-size: 1.15rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Chat Simulator */
        .chat-container {
            display: flex;
            flex-direction: column;
            height: 480px;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding-right: 6px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-bottom: 16px;
        }

        .chat-msg {
            display: flex;
            flex-direction: column;
            gap: 6px;
            max-width: 92%;
        }

        .chat-msg.user {
            align-self: flex-end;
        }

        .chat-msg.user .bubble {
            background: linear-gradient(135deg, var(--accent-indigo), var(--accent-purple));
            color: #ffffff;
            border-radius: 16px 16px 4px 16px;
            padding: 12px 16px;
            font-size: 0.92rem;
        }

        .chat-msg.agent {
            align-self: flex-start;
        }

        .chat-msg.agent .bubble {
            background: #1e293b;
            border: 1px solid var(--border-highlight);
            color: var(--text-primary);
            border-radius: 16px 16px 16px 4px;
            padding: 14px 18px;
            font-size: 0.92rem;
        }

        .agent-thought-box {
            background: rgba(15, 23, 42, 0.8);
            border-left: 3px solid var(--accent-cyan);
            padding: 10px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            color: #94a3b8;
            margin-bottom: 8px;
        }

        .tool-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(139, 92, 246, 0.2);
            border: 1px solid rgba(139, 92, 246, 0.4);
            color: #c4b5fd;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            font-family: 'Fira Code', monospace;
            margin-bottom: 6px;
        }

        .prompt-chips {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }

        .chip {
            background: rgba(30, 41, 59, 0.8);
            border: 1px solid var(--border);
            color: var(--text-secondary);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.78rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .chip:hover {
            border-color: var(--accent-indigo);
            color: #ffffff;
            background: var(--surface-hover);
        }

        .chat-input-row {
            display: flex;
            gap: 10px;
        }

        .chat-input-row input {
            flex: 1;
            background: #0f172a;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px 16px;
            color: #ffffff;
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.2s;
        }

        .chat-input-row input:focus {
            border-color: var(--accent-indigo);
        }

        .btn {
            background: linear-gradient(135deg, var(--accent-indigo), var(--accent-purple));
            color: #ffffff;
            border: none;
            border-radius: 10px;
            padding: 12px 20px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: opacity 0.2s ease, transform 0.1s;
        }

        .btn:hover {
            opacity: 0.92;
            transform: translateY(-1px);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--border-highlight);
            color: var(--text-secondary);
        }

        .btn-outline:hover {
            background: var(--surface-hover);
            color: #ffffff;
        }

        /* Tool Tabs Tester */
        .tabs-nav {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 8px;
            overflow-x: auto;
        }

        .tab-btn {
            background: transparent;
            border: none;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.85rem;
            padding: 8px 14px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .tab-btn.active {
            background: rgba(99, 102, 241, 0.15);
            color: #a5b4fc;
        }

        .tool-form {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .form-row {
            display: flex;
            gap: 12px;
        }

        .form-group {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .form-control {
            background: #0f172a;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px 12px;
            color: #ffffff;
            font-size: 0.88rem;
            outline: none;
        }

        .form-control:focus {
            border-color: var(--accent-indigo);
        }

        /* JSON Output Box */
        .json-box {
            background: #090d16;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 14px;
            color: #38bdf8;
            font-size: 0.8rem;
            max-height: 220px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-break: break-word;
            margin-top: 14px;
        }

        /* Table */
        .table-wrap {
            overflow-x: auto;
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
            text-align: left;
        }

        th {
            background: #0f172a;
            padding: 12px 14px;
            color: var(--text-muted);
            font-weight: 600;
            border-bottom: 1px solid var(--border);
            text-transform: uppercase;
            font-size: 0.72rem;
            letter-spacing: 0.5px;
        }

        td {
            padding: 12px 14px;
            border-bottom: 1px solid var(--border);
            color: var(--text-secondary);
        }

        tr:hover td {
            background: rgba(30, 41, 59, 0.4);
            color: var(--text-primary);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-confirmed {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .status-cancelled {
            background: rgba(244, 63, 94, 0.15);
            color: #fb7185;
            border: 1px solid rgba(244, 63, 94, 0.3);
        }

        .footer {
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 30px;
        }

        .footer a {
            color: var(--accent-cyan);
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="container">

    <!-- Hero Header -->
    <header class="hero">
        <div class="badge-tag">⚡ AI Agent Development Suite</div>
        <h1>Hotel Agent Backend & Tool Suite</h1>
        <p>A hands-on reference architecture demonstrating how Autonomous LLM Agents connect to Laravel backends using structured Tool Calling (Function Calling).</p>
    </header>

    <!-- 4 Tools Summary Cards -->
    <div class="tools-grid">
        <div class="tool-pill">
            <span class="icon">🔍</span>
            <h3>check_availability <span class="tool-method">POST</span></h3>
            <p>Inspects live room inventory, capacity, amenities, and dynamic nightly rates for any given date.</p>
        </div>
        <div class="tool-pill">
            <span class="icon">✍️</span>
            <h3>create_booking <span class="tool-method">POST</span></h3>
            <p>Autonomous reservation maker with conflict detection, validation, and confirmation generation.</p>
        </div>
        <div class="tool-pill">
            <span class="icon">📋</span>
            <h3>get_booking_details <span class="tool-method">POST</span></h3>
            <p>Flexible multi-field lookup query tool to find reservations by ID or customer email address.</p>
        </div>
        <div class="tool-pill">
            <span class="icon">❌</span>
            <h3>cancel_booking <span class="tool-method">POST</span></h3>
            <p>Lifecycle state manager that handles cancellation reasoning and automated refund execution.</p>
        </div>
    </div>

    <!-- Main Section: Chat Simulator + Tool Runner -->
    <div class="grid-2">

        <!-- 1. AI Agent Reasoning & Chat Simulator -->
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">
                    <span>🤖</span>
                    <span>AI Agent Chat Simulator</span>
                </div>
                <span style="font-size: 0.78rem; color: var(--accent-emerald); font-weight: 600;">● Online</span>
            </div>

            <div class="chat-container">
                <div class="chat-messages" id="chatMessages">
                    <div class="chat-msg agent">
                        <div class="bubble">
                            👋 Hello! I am the Hotel AI Concierge Agent. I can check room availability, make reservations, search your bookings, or process cancellations. Try prompting me below!
                        </div>
                    </div>
                </div>

                <div class="prompt-chips">
                    <span class="chip" onclick="setPrompt('Is the deluxe room available on 2026-09-01?')">🔍 Check Deluxe</span>
                    <span class="chip" onclick="setPrompt('Book an executive suite on 2026-11-15 for Alex Morgan')">✍️ Book Suite</span>
                    <span class="chip" onclick="setPrompt('Lookup reservation details for alice@example.com')">📋 Lookup Booking</span>
                    <span class="chip" onclick="setPrompt('Cancel booking #1')">❌ Cancel #1</span>
                </div>

                <div class="chat-input-row">
                    <input type="text" id="agentInput" placeholder="Ask the agent anything (e.g. check availability, book room...)" onkeydown="if(event.key==='Enter') sendAgentMessage()">
                    <button class="btn" id="sendBtn" onclick="sendAgentMessage()">Send</button>
                </div>
            </div>
        </div>

        <!-- 2. Interactive Tool Runner -->
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">
                    <span>🛠️</span>
                    <span>Direct Tool Execution Hub</span>
                </div>
                <button class="btn btn-outline" style="padding: 4px 10px; font-size: 0.75rem;" onclick="copySchema()">📋 Copy Schema</button>
            </div>

            <div class="tabs-nav">
                <button class="tab-btn active" onclick="switchToolTab('check')">check_availability</button>
                <button class="tab-btn" onclick="switchToolTab('book')">create_booking</button>
                <button class="tab-btn" onclick="switchToolTab('details')">get_booking_details</button>
                <button class="tab-btn" onclick="switchToolTab('cancel')">cancel_booking</button>
            </div>

            <!-- Tab 1: Check Availability Form -->
            <div id="tab-check" class="tool-form">
                <div class="form-row">
                    <div class="form-group">
                        <label>Date (YYYY-MM-DD)</label>
                        <input type="date" id="checkDate" class="form-control" value="2026-09-01">
                    </div>
                    <div class="form-group">
                        <label>Room Category (Optional)</label>
                        <select id="checkRoom" class="form-control">
                            <option value="">All Categories</option>
                            <option value="standard">Standard Queen ($99)</option>
                            <option value="deluxe">Deluxe King ($150)</option>
                            <option value="suite">Executive Suite ($250)</option>
                            <option value="penthouse">Presidential Penthouse ($500)</option>
                        </select>
                    </div>
                </div>
                <button class="btn" onclick="runCheckAvailability()">Execute check_availability</button>
            </div>

            <!-- Tab 2: Create Booking Form -->
            <div id="tab-book" class="tool-form" style="display: none;">
                <div class="form-row">
                    <div class="form-group">
                        <label>Guest Name</label>
                        <input type="text" id="bookName" class="form-control" value="Sophia Taylor">
                    </div>
                    <div class="form-group">
                        <label>Guest Email</label>
                        <input type="email" id="bookEmail" class="form-control" value="sophia@example.com">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" id="bookDate" class="form-control" value="2026-10-10">
                    </div>
                    <div class="form-group">
                        <label>Room Category</label>
                        <select id="bookRoom" class="form-control">
                            <option value="standard">Standard Queen ($99)</option>
                            <option value="deluxe">Deluxe King ($150)</option>
                            <option value="suite">Executive Suite ($250)</option>
                            <option value="penthouse">Presidential Penthouse ($500)</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Special Requests</label>
                    <input type="text" id="bookRequests" class="form-control" value="Quiet room with lake view">
                </div>
                <button class="btn" onclick="runCreateBooking()">Execute create_booking</button>
            </div>

            <!-- Tab 3: Get Booking Details Form -->
            <div id="tab-details" class="tool-form" style="display: none;">
                <div class="form-row">
                    <div class="form-group">
                        <label>Booking ID</label>
                        <input type="number" id="searchId" class="form-control" placeholder="e.g. 1">
                    </div>
                    <div class="form-group">
                        <label>Or Customer Email</label>
                        <input type="email" id="searchEmail" class="form-control" placeholder="alice@example.com">
                    </div>
                </div>
                <button class="btn" onclick="runGetBookingDetails()">Execute get_booking_details</button>
            </div>

            <!-- Tab 4: Cancel Booking Form -->
            <div id="tab-cancel" class="tool-form" style="display: none;">
                <div class="form-row">
                    <div class="form-group">
                        <label>Booking ID to Cancel</label>
                        <input type="number" id="cancelId" class="form-control" value="1">
                    </div>
                    <div class="form-group">
                        <label>Cancellation Reason</label>
                        <input type="text" id="cancelReason" class="form-control" value="Travel schedule changed">
                    </div>
                </div>
                <button class="btn" style="background: linear-gradient(135deg, #f43f5e, #e11d48);" onclick="runCancelBooking()">Execute cancel_booking</button>
            </div>

            <!-- Output Box -->
            <pre class="json-box" id="toolOutput">// Raw Tool Execution JSON response will display here...</pre>
        </div>

    </div>

    <!-- Live Database Records Inspector -->
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">
                <span>🗄️</span>
                <span>Live Database Records (SQLite)</span>
            </div>
            <button class="btn btn-outline" style="padding: 6px 14px; font-size: 0.8rem;" onclick="loadLiveBookings()">🔄 Refresh Table</button>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Guest Name</th>
                        <th>Guest Email</th>
                        <th>Room</th>
                        <th>Date</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Special Requests</th>
                    </tr>
                </thead>
                <tbody id="bookingsTableBody">
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-muted);">Loading live database records...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <footer class="footer">
        Built for <strong>AI Agent Development</strong> in Laravel • Endpoints: <a href="/api/agent/tools" target="_blank">/api/agent/tools</a> • <a href="/api/agent/live-bookings" target="_blank">/api/agent/live-bookings</a>
    </footer>

</div>

<script>
    // Tab switching
    function switchToolTab(tab) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tool-form').forEach(f => f.style.display = 'none');

        if (tab === 'check') {
            document.querySelectorAll('.tab-btn')[0].classList.add('active');
            document.getElementById('tab-check').style.display = 'flex';
        } else if (tab === 'book') {
            document.querySelectorAll('.tab-btn')[1].classList.add('active');
            document.getElementById('tab-book').style.display = 'flex';
        } else if (tab === 'details') {
            document.querySelectorAll('.tab-btn')[2].classList.add('active');
            document.getElementById('tab-details').style.display = 'flex';
        } else if (tab === 'cancel') {
            document.querySelectorAll('.tab-btn')[3].classList.add('active');
            document.getElementById('tab-cancel').style.display = 'flex';
        }
    }

    function setPrompt(text) {
        document.getElementById('agentInput').value = text;
        document.getElementById('agentInput').focus();
    }

    // Chat Simulator
    async function sendAgentMessage() {
        const input = document.getElementById('agentInput');
        const text = input.value.trim();
        if (!text) return;

        const chatBox = document.getElementById('chatMessages');

        // Append user message
        const userDiv = document.createElement('div');
        userDiv.className = 'chat-msg user';
        userDiv.innerHTML = `<div class="bubble">${escapeHtml(text)}</div>`;
        chatBox.appendChild(userDiv);
        input.value = '';
        chatBox.scrollTop = chatBox.scrollHeight;

        // Add loading placeholder
        const agentDiv = document.createElement('div');
        agentDiv.className = 'chat-msg agent';
        agentDiv.innerHTML = `<div class="bubble"><span style="color: var(--text-muted)">Thinking & reasoning...</span></div>`;
        chatBox.appendChild(agentDiv);
        chatBox.scrollTop = chatBox.scrollHeight;

        try {
            const res = await fetch('/api/agent/chat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ message: text })
            });
            const data = await res.json();

            // Format Agent Thought + Tool Info + Reply
            agentDiv.innerHTML = `
                <div class="agent-thought-box">
                    <strong>🧠 Reasoning:</strong> ${escapeHtml(data.agent_reasoning.thought)}
                </div>
                <div class="tool-badge">
                    ⚡ Tool Called: ${data.agent_reasoning.selected_tool}
                </div>
                <div class="bubble">
                    ${escapeHtml(data.agent_response)}
                </div>
            `;
            chatBox.scrollTop = chatBox.scrollHeight;

            // Refresh table automatically
            loadLiveBookings();
        } catch (err) {
            agentDiv.innerHTML = `<div class="bubble" style="color: #fb7185">Error connecting to agent backend: ${err.message}</div>`;
        }
    }

    // Tool 1: Check Availability
    async function runCheckAvailability() {
        const date = document.getElementById('checkDate').value;
        const room_type = document.getElementById('checkRoom').value;
        const payload = { date };
        if (room_type) payload.room_type = room_type;

        executeDirectTool('/api/agent/check-availability', payload);
    }

    // Tool 2: Create Booking
    async function runCreateBooking() {
        const payload = {
            customer_name: document.getElementById('bookName').value,
            customer_email: document.getElementById('bookEmail').value,
            date: document.getElementById('bookDate').value,
            room_type: document.getElementById('bookRoom').value,
            special_requests: document.getElementById('bookRequests').value,
        };
        executeDirectTool('/api/agent/create-booking', payload);
    }

    // Tool 3: Get Booking Details
    async function runGetBookingDetails() {
        const booking_id = document.getElementById('searchId').value;
        const customer_email = document.getElementById('searchEmail').value;
        const payload = {};
        if (booking_id) payload.booking_id = parseInt(booking_id);
        if (customer_email) payload.customer_email = customer_email;

        executeDirectTool('/api/agent/get-booking-details', payload);
    }

    // Tool 4: Cancel Booking
    async function runCancelBooking() {
        const booking_id = parseInt(document.getElementById('cancelId').value);
        const reason = document.getElementById('cancelReason').value;
        const payload = { booking_id, reason };

        executeDirectTool('/api/agent/cancel-booking', payload);
    }

    async function executeDirectTool(url, payload) {
        const out = document.getElementById('toolOutput');
        out.textContent = '// Sending request to ' + url + '...';

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            out.textContent = JSON.stringify(data, null, 2);
            loadLiveBookings();
        } catch (e) {
            out.textContent = '// Error: ' + e.message;
        }
    }

    // Load Live DB Bookings
    async function loadLiveBookings() {
        const tbody = document.getElementById('bookingsTableBody');
        try {
            const res = await fetch('/api/agent/live-bookings');
            const data = await res.json();

            if (!data.bookings || data.bookings.length === 0) {
                tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; color: var(--text-muted);">No bookings found in database.</td></tr>`;
                return;
            }

            tbody.innerHTML = data.bookings.map(b => `
                <tr>
                    <td style="font-weight: 700; font-family: 'Fira Code', monospace; color: #a5b4fc;">#${b.id}</td>
                    <td style="font-weight: 600; color: #ffffff;">${escapeHtml(b.customer_name)}</td>
                    <td>${escapeHtml(b.customer_email)}</td>
                    <td><span style="text-transform: capitalize; font-weight: 500;">${escapeHtml(b.room_type)}</span></td>
                    <td style="font-family: 'Fira Code', monospace;">${b.date ? b.date.substring(0, 10) : '-'}</td>
                    <td style="font-weight: 600; color: #34d399;">$${parseFloat(b.price).toFixed(2)}</td>
                    <td>
                        <span class="status-badge ${b.status === 'confirmed' ? 'status-confirmed' : 'status-cancelled'}">
                            ${b.status}
                        </span>
                    </td>
                    <td style="font-size: 0.8rem; color: var(--text-muted);">${escapeHtml(b.special_requests || '—')}</td>
                </tr>
            `).join('');
        } catch (e) {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; color: #fb7185;">Failed to load live database records.</td></tr>`;
        }
    }

    // Copy Tools Schema
    async function copySchema() {
        try {
            const res = await fetch('/api/agent/tools');
            const data = await res.json();
            await navigator.clipboard.writeText(JSON.stringify(data.tools, null, 2));
            alert('OpenAI Function Calling Tool Schemas copied to clipboard!');
        } catch (e) {
            alert('Error copying schema: ' + e.message);
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // Initial Load
    loadLiveBookings();
</script>

</body>
</html>
