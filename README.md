# 🤖 Laravel AI Agent Development Suite & Toolset

A reference architecture and learning playground for building **AI Agents** with **Laravel**.

This project demonstrates how Autonomous AI Agents (using OpenAI Function Calling, Anthropic Tools, Gemini, LangChain, AutoGPT, or custom agent loops) connect to Laravel backend APIs to perform deterministic database operations and business logic.

---

## 🛠️ The 4 Core Agent Tools

| # | Tool Name | Endpoint | Method | Purpose |
|---|-----------|----------|--------|---------|
| 1 | `check_availability` | `/api/agent/check-availability` | `POST` | Check room availability, capacity, amenities, and dynamic nightly pricing for any date. |
| 2 | `create_booking` | `/api/agent/create-booking` | `POST` | Create a confirmed reservation with conflict checking and confirmation codes (`RES-XXXXX`). |
| 3 | `get_booking_details` | `/api/agent/get-booking-details` | `POST` / `GET` | Search and retrieve reservation details by `booking_id` or `customer_email`. |
| 4 | `cancel_booking` | `/api/agent/cancel-booking` | `POST` | Cancel an existing reservation, update lifecycle status, and initiate automated refund. |

---

## 🚀 Key Agent Development Features

### 1. 📜 OpenAI-Compatible Tool Schema Catalog
- **Endpoint**: `GET /api/agent/tools`
- Returns full JSON Schema definitions of all 4 tools with parameter descriptions, types, enums, and required fields ready to be plugged into OpenAI API (`tools` parameter) or any agent framework.

### 2. ⚡ Universal Tool Dispatcher
- **Endpoint**: `POST /api/agent/execute`
- Allows any AI agent to dispatch tool execution requests uniformly:
```json
{
  "tool": "check_availability",
  "arguments": {
    "date": "2026-09-01",
    "room_type": "deluxe"
  }
}
```

### 3. 🧠 Agent Chat & Reasoning Simulator
- **Endpoint**: `POST /api/agent/chat`
- Demonstrates an agent reasoning loop:
  1. Receives natural language user prompt.
  2. Extracts entities (dates, room types, emails, booking IDs).
  3. Formulates reasoning `thought`.
  4. Selects and calls the correct tool.
  5. Synthesizes a natural language response based on raw tool output.

### 4. 🖥️ Interactive Developer Playground
- Open `/` in your browser to access the visual developer playground:
  - Talk to the simulated AI concierge.
  - Test each tool individually with sample inputs.
  - Copy tool JSON schemas with 1 click.
  - Live inspector viewing SQLite database records updated in real time.

---

## 🏃 Getting Started

### 1. Database Setup & Seed
```bash
php artisan migrate:fresh --seed
```

### 2. Run the Development Server
```bash
php artisan serve
```
Then visit `http://localhost:8000` to interact with the learning playground.

### 3. Run Automated Tests
```bash
php tests/scratch_test.php
```

---

## 📖 How AI Agents Use These Tools

When using OpenAI's chat completions API with Tools:

```php
$tools = Http::get('http://localhost:8000/api/agent/tools')->json()['tools'];

$response = $openai->chat()->create([
    'model' => 'gpt-4o',
    'messages' => [
        ['role' => 'system', 'content' => 'You are a helpful hotel booking concierge.'],
        ['role' => 'user', 'content' => 'Book a deluxe room on 2026-09-15 for Alex Rivera (alex@example.com)']
    ],
    'tools' => $tools,
]);

// When the model returns a tool_call:
$toolCall = $response->choices[0]->message->toolCalls[0];

// Execute tool in Laravel:
$toolOutput = Http::post('http://localhost:8000/api/agent/execute', [
    'tool' => $toolCall->function->name,
    'arguments' => json_decode($toolCall->function->arguments, true),
]);
```
