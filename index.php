<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vortexa AI - Smart Virtual Assistant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --bg-color: #0d1117; --card-bg: #161b22; --accent-color: #58a6ff; --text-main: #c9d1d9; }
        body { background-color: var(--bg-color); color: var(--text-main); font-family: 'Segoe UI', Roboto, sans-serif; height: 100vh; margin: 0; display: flex; flex-direction: column; }
        .app-bar { background: var(--card-bg); padding: 1rem; border-bottom: 1px solid #30363d; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.5); }
        .app-bar h1 { font-size: 1.2rem; color: var(--accent-color); margin: 0; font-weight: 600; text-transform: uppercase; }
        .chat-box { flex: 1; overflow-y: auto; padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem; }
        .msg { max-width: 80%; padding: 0.8rem 1.2rem; border-radius: 15px; font-size: 0.95rem; line-height: 1.5; animation: fadeIn 0.4s ease; }
        .user-msg { background: #238636; align-self: flex-start; border-bottom-left-radius: 2px; color: white; }
        .ai-msg { background: var(--card-bg); border: 1px solid #30363d; align-self: flex-end; border-bottom-right-radius: 2px; }
        .input-container { background: var(--card-bg); padding: 1rem; border-top: 1px solid #30363d; }
        .input-group { background: var(--bg-color); border-radius: 30px; border: 1px solid #30363d; padding: 5px 15px; }
        .input-group input { background: transparent; border: none; color: white; box-shadow: none !important; }
        .btn-send { background: var(--accent-color); border: none; width: 40px; height: 40px; border-radius: 50%; color: white; transition: 0.3s; }
        .btn-send:hover { opacity: 0.8; transform: scale(1.05); }
        .typing-indicator { display: none; font-size: 0.8rem; color: var(--accent-color); margin-bottom: 0.5rem; padding-right: 10px; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

<div class="app-bar">
    <h1><i class="fas fa-robot"></i> Vortexa AI Assistant</h1>
</div>

<div class="chat-box" id="chatDisplay">
    <div class="msg ai-msg">مرحباً بك في <b>Vortexa AI</b>. كيف يمكنني مساعدتك في برمجياتك أو أبحاثك اليوم؟</div>
</div>

<div class="input-container">
    <div id="loader" class="typing-indicator"><i class="fas fa-sync fa-spin"></i> Vortexa تفكر الآن...</div>
    <div class="input-group">
        <input type="text" id="userInput" class="form-control" placeholder="اكتب سؤالك هنا..." autocomplete="off">
        <button class="btn-send" onclick="processMessage()"><i class="fas fa-paper-plane"></i></button>
    </div>
</div>

<script>
let memory = [];

async function processMessage() {
    const inputField = document.getElementById('userInput');
    const query = inputField.value.trim();
    if(!query) return;

    const display = document.getElementById('chatDisplay');
    const loader = document.getElementById('loader');

    display.innerHTML += `<div class="msg user-msg">${query}</div>`;
    inputField.value = '';
    loader.style.display = 'block';
    display.scrollTop = display.scrollHeight;

    try {
        const params = new URLSearchParams();
        params.append('query', query);
        params.append('history', JSON.stringify(memory));

        const res = await fetch('process.php', { method: 'POST', body: params });
        const data = await res.json();
        
        loader.style.display = 'none';
        display.innerHTML += `<div class="msg ai-msg">${data.answer}</div>`;
        
        memory.push({role: "user", content: query}, {role: "assistant", content: data.answer});
        if(memory.length > 6) memory.splice(0, 2);
        display.scrollTop = display.scrollHeight;

    } catch (err) {
        loader.style.display = 'none';
        display.innerHTML += `<div class="msg ai-msg" style="color:#f85149">فشل الاتصال بمحرك الذكاء الاصطناعي.</div>`;
    }
}

document.getElementById('userInput').addEventListener('keypress', e => { if(e.key === 'Enter') processMessage(); });
</script>
</body>
</html>
  
