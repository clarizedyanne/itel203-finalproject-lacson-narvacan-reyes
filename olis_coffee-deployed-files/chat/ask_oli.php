<?php
// chatbot.php - AI Chatbot for Customer Inquiries
session_start();
require_once '../includes/Auth.php';
Auth::requireLogin();

$userName = htmlspecialchars($_SESSION['user_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <link rel="icon" type="image/png" href="../../assets/logo.png">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ask Oli – AI Chatbot · Oli's SelfieTea & Coffee</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../../css/style.css">
  <style>
    .chat-container {
      height: 420px;
      overflow-y: auto;
      background: #f8fdf5;
      border-radius: 12px;
      padding: 1rem;
      border: 1px solid #d1e7c8;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    .chat-bubble {
      max-width: 80%;
      padding: 10px 14px;
      border-radius: 16px;
      font-size: 0.9rem;
      line-height: 1.5;
      word-break: break-word;
    }
    .chat-bubble.user {
      align-self: flex-end;
      background: var(--green-dark);
      color: white;
      border-bottom-right-radius: 4px;
    }
    .chat-bubble.bot {
      align-self: flex-start;
      background: white;
      color: var(--text-dark);
      border: 1px solid #d1e7c8;
      border-bottom-left-radius: 4px;
    }
    .chat-bubble.bot .bot-name {
      font-size: 0.7rem;
      color: var(--green-mid);
      font-weight: 700;
      margin-bottom: 4px;
    }
    .chat-bubble.typing {
      background: white;
      border: 1px solid #d1e7c8;
      align-self: flex-start;
    }
    .typing-dot {
      display: inline-block;
      width: 8px; height: 8px;
      background: var(--green-mid);
      border-radius: 50%;
      animation: bounce 1.2s infinite;
      margin: 0 2px;
    }
    .typing-dot:nth-child(2) { animation-delay: 0.2s; }
    .typing-dot:nth-child(3) { animation-delay: 0.4s; }
    @keyframes bounce {
      0%, 80%, 100% { transform: translateY(0); }
      40% { transform: translateY(-6px); }
    }
    .quick-btn {
      font-size: 0.78rem;
      padding: 5px 12px;
      border-radius: 20px;
      border: 1.5px solid var(--green-mid);
      background: transparent;
      color: var(--green-mid);
      cursor: pointer;
      transition: all 0.2s;
      font-family: 'Lato', sans-serif;
    }
    .quick-btn:hover {
      background: var(--green-dark);
      border-color: var(--green-dark);
      color: white;
    }
    #chatInput {
      border: 2px solid #d1e7c8;
      border-radius: 12px;
      padding: 0.6rem 1rem;
      resize: none;
    }
    #chatInput:focus {
      border-color: var(--green-mid);
      box-shadow: 0 0 0 3px rgba(74,124,53,0.15);
    }
    #sendBtn {
      background: var(--green-dark);
      color: white;
      border: none;
      border-radius: 12px;
      padding: 0 1.2rem;
      font-weight: 700;
      transition: opacity 0.2s;
    }
    #sendBtn:hover { opacity: 0.85; color: white; }
    #sendBtn:disabled { opacity: 0.5; }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand" href="index.php">
      <img src="../assets/logo.png" alt="Oli's SelfieTea & Coffee" style="height:50px;width:auto;">
      <div>Oli's SelfieTea & Coffee <span class="sub">· Est. 2019</span></div>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto align-items-center gap-1">
        <li class="nav-item"><a class="nav-link" href="../index.php"><i class="bi bi-house me-1"></i>Home</a></li>
        <li class="nav-item"><a class="nav-link" href="../menu.php"><i class="bi bi-journal-text me-1"></i>Menu</a></li>
        <li class="nav-item"><a class="nav-link" href="../book_reservation.php"><i class="bi bi-calendar-check me-1"></i>Reservations</a></li>
        <li class="nav-item"><a class="nav-link active" href="chatbot/chatbot.php"><i class="bi bi-chat-dots me-1"></i>Ask Oli</a></li>
        <li class="nav-item"><a class="nav-link" href="../about.php"><i class="bi bi-info-circle me-1"></i>About</a></li>
        <li class="nav-item"><a class="nav-link" href="../contact.php"><i class="bi bi-geo-alt me-1"></i>Contact</a></li>
        <li class="nav-item"><a class="nav-link" href="../profile.php"><i class="bi bi-person-circle me-1"></i>My Profile</a></li>
                <?php if (Auth::isAdmin()): ?>
        <li class="nav-item">
          <a class="nav-link" href="../admin/dashboard.php"
             style="background:var(--gold);color:var(--green-dark);border-radius:20px;padding:5px 14px;font-weight:700;font-size:0.82rem;">
            <i class="bi bi-speedometer2 me-1"></i>Admin Panel
          </a>
        </li>
        <?php endif; ?>
        <li class="nav-item">
          <a class="btn-logout nav-link" href="logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- HERO MINI -->
<div style="background: linear-gradient(135deg, var(--green-dark), #1a3510); padding:40px 0 30px; color:var(--cream);">
  <div class="container">
    <p style="font-size:0.8rem; letter-spacing:3px; text-transform:uppercase; color:rgba(245,240,232,0.6); margin-bottom:4px;">AI Assistant</p>
    <h2 style="font-family:'Playfair Display',serif; font-weight:700;">Ask Oli <span style="color:var(--gold);">✨</span></h2>
    <p style="color:rgba(245,240,232,0.75); margin-top:6px; font-size:0.95rem;">Your AI-powered menu guide and inquiry assistant</p>
  </div>
</div>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-7">

      <div class="card border-0 shadow-sm">
        <div class="card-header d-flex align-items-center gap-2" style="background:var(--green-dark); color:white;">
          <div style="width:36px;height:36px;background:var(--gold);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.1rem;">☕</div>
          <div>
            <div style="font-weight:700; font-size:0.95rem;">Oli Bot</div>
            <div style="font-size:0.7rem; color:var(--green-light);">● Online · AI-powered</div>
          </div>
        </div>

        <div class="card-body p-3">
          <!-- Chat window -->
          <div class="chat-container" id="chatWindow">
            <div class="chat-bubble bot">
              <div class="bot-name">☕ Oli Bot</div>
              Hello, <?= $userName ?>! 👋 I'm <strong>Oli Bot</strong>, your AI assistant for Oli's SelfieTea & Coffee.
              I can help you with our menu, prices, reservations, and more. How can I help you today?
            </div>
          </div>

          <!-- Quick replies -->
          <div class="d-flex flex-wrap gap-2 mt-3 mb-3">
            <button class="quick-btn" onclick="quickSend('What are your best sellers?')">🔥 Best sellers</button>
            <button class="quick-btn" onclick="quickSend('What drinks do you have?')">☕ Drinks menu</button>
            <button class="quick-btn" onclick="quickSend('How do I make a reservation?')">📅 Reservations</button>
            <button class="quick-btn" onclick="quickSend('What are your snacks?')">🍟 Snacks</button>
            <button class="quick-btn" onclick="quickSend('What pizza do you offer?')">🍕 Pizza</button>
            <button class="quick-btn" onclick="quickSend('What are your store hours?')">🕐 Store hours</button>
          </div>

          <!-- Input area -->
          <div class="d-flex gap-2">
            <textarea id="chatInput" class="form-control" rows="2"
                      placeholder="Ask about our menu, prices, reservations..."></textarea>
            <button id="sendBtn" class="btn" onclick="sendMessage()">
              <i class="bi bi-send-fill"></i>
            </button>
          </div>
          <div class="mt-2 text-muted" style="font-size:0.72rem; text-align:center;">
            <i class="bi bi-robot me-1"></i>Powered by Claude AI · Responses may not be 100% accurate
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<footer>
  <strong>Oli's SelfieTea & Coffee</strong> · Est. 2019 · All rights reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>

let chatHistory = [];

function appendMessage(role, content) {
  const win = document.getElementById('chatWindow');
  const div = document.createElement('div');
  div.className = 'chat-bubble ' + role;
  if (role === 'bot') {
    div.innerHTML = '<div class="bot-name">☕ Oli Bot</div>' + escapeHtml(content).replace(/\n/g, '<br>');
  } else {
    div.textContent = content;
  }
  win.appendChild(div);
  win.scrollTop = win.scrollHeight;
  return div;
}

function escapeHtml(text) {
  const d = document.createElement('div');
  d.appendChild(document.createTextNode(text));
  return d.innerHTML;
}

function showTyping() {
  const win = document.getElementById('chatWindow');
  const div = document.createElement('div');
  div.className = 'chat-bubble typing';
  div.id = 'typingIndicator';
  div.innerHTML = '<span class="typing-dot"></span><span class="typing-dot"></span><span class="typing-dot"></span>';
  win.appendChild(div);
  win.scrollTop = win.scrollHeight;
}

function hideTyping() {
  const t = document.getElementById('typingIndicator');
  if (t) t.remove();
}

function quickSend(text) {
  document.getElementById('chatInput').value = text;
  sendMessage();
}

// SYSTEM_PROMPT moved to Cloudflare Worker
const SYSTEM_PROMPT_UNUSED = `You are Oli Bot, a friendly and helpful AI assistant for Oli's SelfieTea & Coffee — a cozy coffee shop established in 2019.

Your job is to help customers with:
- Menu inquiries and recommendations
- Pricing information
- Reservation questions
- General shop information

Here is the FULL MENU:

SNACKS:
- Nachos — ₱198
- Chicken Fingers — ₱189
- Cheesy Bacon Fries — ₱198
- Fish & Fries — ₱198
- Flavored Fries (Barbecue/Cheese/Sour Cream) — ₱159
- Flavored Mojos (Barbecue/Cheese/Sour Cream) — ₱189

PASTA (Served with Garlic Bread):
- Gourmet Tuyo Pasta — ₱189
- Alfredo (White Sauce) — ₱194
- Meat Sauce Spaghetti — ₱189
- Lasagna — ₱194
- Aligue Pasta — ₱189
- Shrimp Aglio Olio — ₱194
- Chicken Oriental Pasta — ₱189

BURGERS/SANDWICHES (Served with Fries):
- Pulled Pork BBQ — ₱189
- Dori Fish Burger — ₱189
- Cheeseburger — ₱194
- Bacon Cheeseburger — ₱209
- Crispy Chicken Burger — ₱194
- Clubhouse Sandwich — ₱194

MAIN MENU - FOR SHARING (Flavored Boneless Chicken Bites):
- Yangnyeom (Spicy Korean) — ₱279
- Garlic Parmesan — ₱279
- Hickory Barbecue — ₱279
- Spicy Salted Egg — ₱279

MAIN MENU - RICE MEAL (Served with buttered vegetables):
- Chicken Fingers w/ Rice — ₱189
- Burger Steak w/ Egg — ₱194
- 2pcs Grilled Porkchop w/ Mushroom Gravy — ₱214
- Chicken Fillet Ala King — ₱199
- Breaded Porkchop w/ Egg — ₱194
- Fish Fillet w/ Rice in Tartar Sauce — ₱194
- Flavored Chicken Bites w/ Rice — ₱199
- 4pcs Chicken Wings w/ Rice — ₱199

CHICKEN WINGS:
- Yangnyeom/Garlic Parmesan/Hickory BBQ/Spicy Salted Egg — 6pcs: ₱239 | 12pcs: ₱459

SALADS/HEALTHY OPTIONS:
- Macaroni Salad — ₱169
- Kani Salad — ₱189
- Chicken Caesar Salad — ₱209

PIZZA (New York Style):
Classic (12\"/16\"): All Cheese ₱329/₱449 | American Ham & Cheese ₱349/₱469 | Hawaiian ₱359/₱479
Premium (12\"/16\"): NY Pepperoni ₱389/₱499 | Hawaiian Supreme ₱399/₱509 | All Meat ₱399/₱509 | NY Special ₱399/₱509 | Carbonara ₱399/₱509 | Pulled Pork BBQ ₱399/₱509
Latest Special (12\"/16\"): 4 Cheese Pizza ₱409/₱529 | Garlic Shrimp Pizza ₱409/₱529

DRINKS:
Artisan Tea (16oz/22oz): Pearl Milk Tea ₱95/₱105 | Earl Grey ₱105/₱115 | Ceylon ₱105/₱115
Milk Tea (16oz/22oz): Wintermelon/Okinawa/Taro ₱85/₱95 | Dark Choco/Matcha/Brown Sugar ₱95/₱105
Hot Drinks (12oz/16oz): Americano ₱105/₱120 | Latte ₱120/₱135 | Cappuccino ₱120/₱135 | Hot Choco ₱125/₱140
Iced Drinks (16oz/22oz): Iced Americano ₱105/₱120 | Iced Latte ₱125/₱140
Ice Blended (16oz/22oz): Choco Chip Mocha/Mocha/Caramel ₱130/₱145

RESERVATIONS:
- 2nd floor only, 4 tables, 5 seats each (max 20 pax per time slot)
- ₱100 non-refundable reservation fee
- Pay via GCash
- Advance booking required (at least 1 day ahead)

STORE INFO:
- Name: Oli's SelfieTea & Coffee
- Established: 2019
- GCash payment accepted

STORE HOURS:
- Monday to Friday: 11:00 AM to 9:00 PM
- Saturday: 11:00 AM to 9:00 PM
- Sunday: 11:00 AM to 9:00 PM

Be warm, helpful, and concise. Use emojis occasionally. If unsure, suggest they contact the shop directly.`;
// API_KEY moved to Cloudflare Worker

async function sendMessage() {
  const input = document.getElementById('chatInput');
  const btn   = document.getElementById('sendBtn');
  const text  = input.value.trim();
  if (!text) return;

  input.value = '';
  btn.disabled = true;

  appendMessage('user', text);
  chatHistory.push({ role: 'user', content: text });

  showTyping();

  try {
    const response = await fetch('https://olis-chatbot.ayis06.workers.dev', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ messages: chatHistory })
    });

    const data = await response.json();
    hideTyping();

    const reply = data?.reply;
    if (reply) {
      appendMessage('bot', reply);
      chatHistory.push({ role: 'assistant', content: reply });
    } else {
      const errMsg = data?.error || 'Sorry, I had trouble responding. Please try again!';
      appendMessage('bot', '⚠️ ' + errMsg);
    }

  } catch (err) {
    hideTyping();
    appendMessage('bot', 'Oops! I seem to be offline right now. Please try again in a moment. 😅');
    console.error(err);
  }

  btn.disabled = false;
  input.focus();
}

// Enter to send (Shift+Enter for new line)
document.getElementById('chatInput').addEventListener('keydown', function(e) {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault();
    sendMessage();
  }
});
</script>
</body>
</html>