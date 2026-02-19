/**
 * RentRide Smart Chatbot Assistant
 * A professional WhatsApp-style AI assistant with backend integration.
 */

class RentRideChatbot {
    constructor() {
        this.isOpen = false;
        this.isTyping = false;
        this.init();
    }

    init() {
        this.injectStyles();
        this.createElements();
        this.addEventListeners();
    }

    injectStyles() {
        if (document.getElementById('rr-chatbot-styles')) return;
        const style = document.createElement('style');
        style.id = 'rr-chatbot-styles';
        style.textContent = `
            .rr-chatbot-fab {
                position: fixed; bottom: 30px; right: 30px;
                width: 65px; height: 65px; border-radius: 50%;
                background: #25d366; color: white;
                display: flex; align-items: center; justify-content: center;
                font-size: 28px; box-shadow: 0 10px 25px rgba(37, 211, 102, 0.4);
                cursor: pointer; z-index: 1000;
                transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            }
            .rr-chatbot-fab:hover { transform: scale(1.1); }
            
            .rr-chatbot-window {
                position: fixed; bottom: 110px; right: 30px;
                width: 380px; height: 550px; background: #e5ddd5;
                border-radius: 12px; box-shadow: 0 15px 50px rgba(0,0,0,0.2);
                display: none; flex-direction: column; overflow: hidden;
                z-index: 1001; animation: rrSlideIn 0.3s ease;
            }
            @keyframes rrSlideIn { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

            .rr-chat-header {
                background: #075e54; color: white; padding: 15px;
                display: flex; align-items: center; gap: 12px;
            }
            .rr-chat-avatar {
                width: 40px; height: 40px; border-radius: 50%; background: #eee;
                display: flex; align-items: center; justify-content: center; color: #075e54;
            }
            
            .rr-chat-body {
                flex: 1; overflow-y: auto; padding: 15px;
                background: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png');
                display: flex; flex-direction: column; gap: 8px;
            }

            .rr-bubble {
                max-width: 85%; padding: 8px 12px; border-radius: 8px;
                font-size: 14.5px; position: relative; line-height: 1.5;
            }
            .rr-bubble-bot { background: white; align-self: flex-start; border-top-left-radius: 0; color: #111; }
            .rr-bubble-user { background: #dcf8c6; align-self: flex-end; border-top-right-radius: 0; color: #111; box-shadow: 0 1px 1px rgba(0,0,0,0.1); }
            
            .rr-time { font-size: 10px; color: #999; margin-top: 4px; text-align: right; }

            .rr-input-area {
                background: #f0f0f0; padding: 10px; display: flex; gap: 8px; align-items: center;
            }
            .rr-input {
                flex: 1; border: none; padding: 10px 15px; border-radius: 20px;
                outline: none; font-size: 15px;
            }
            .rr-send-btn {
                background: #075e54; color: white; border: none;
                width: 40px; height: 40px; border-radius: 50%;
                cursor: pointer; display: flex; align-items: center; justify-content: center;
            }

            .typing { display: flex; align-items: center; gap: 4px; height: 20px; padding: 10px; background: white; border-radius: 10px; width: fit-content; margin-bottom: 10px; }
            .dot { width: 6px; height: 6px; background: #999; border-radius: 50%; opacity: 0.4; animation: dotPulse 1.5s infinite; }
            .dot:nth-child(2) { animation-delay: 0.2s; }
            .dot:nth-child(3) { animation-delay: 0.4s; }
            @keyframes dotPulse { 0%, 100% { opacity: 0.4; transform: scale(1); } 50% { opacity: 1; transform: scale(1.2); } }
        `;
        document.head.appendChild(style);
    }

    createElements() {
        this.fab = document.createElement('div');
        this.fab.className = 'rr-chatbot-fab';
        this.fab.innerHTML = '<span>💬</span>';
        document.body.appendChild(this.fab);

        this.window = document.createElement('div');
        this.window.className = 'rr-chatbot-window';
        this.window.innerHTML = `
            <div class="rr-chat-header">
                <div class="rr-chat-avatar">🚗</div>
                <div style="flex: 1;">
                    <div style="font-weight: 600; font-size: 16px;">RentRide Assistant</div>
                    <div style="font-size: 12px; color: #dcf8c6;">Online</div>
                </div>
                <span id="close-chat" style="cursor:pointer; font-size: 24px;">&times;</span>
            </div>
            <div class="rr-chat-body" id="chat-body">
                <div class="rr-bubble rr-bubble-bot">
                    Hi! 🚗 Welcome to RentRide. How can I assist you with your car rental today?
                    <div class="rr-time">${new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</div>
                </div>
            </div>
            <form class="rr-input-area" id="chat-form">
                <input type="text" id="chat-input" class="rr-input" placeholder="Type a message..." autocomplete="off">
                <button type="submit" class="rr-send-btn">➤</button>
            </form>
        `;
        document.body.appendChild(this.window);
    }

    addEventListeners() {
        this.fab.onclick = () => this.toggleChat();
        const closeBtn = document.getElementById('close-chat');
        if (closeBtn) closeBtn.onclick = () => this.toggleChat();

        const form = document.getElementById('chat-form');
        if (form) {
            form.onsubmit = (e) => {
                e.preventDefault();
                this.handleSend();
            };
        }
    }

    toggleChat() {
        this.isOpen = !this.isOpen;
        this.window.style.display = this.isOpen ? 'flex' : 'none';
        if (this.isOpen) {
            document.getElementById('chat-input').focus();
            this.fab.style.transform = 'scale(0)';
        } else {
            this.fab.style.transform = 'scale(1)';
        }
    }

    async handleSend() {
        const input = document.getElementById('chat-input');
        const text = input.value.trim();
        if (!text || this.isTyping) return;

        this.addMessage(text, 'user');
        input.value = '';

        this.showTyping(true);

        try {
            const response = await fetch('/chatbot_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: text })
            });
            const data = await response.json();

            setTimeout(() => {
                this.showTyping(false);
                this.addMessage(data.reply || "I'm not sure how to respond to that.", 'bot');
            }, 800);

        } catch (error) {
            this.showTyping(false);
            this.addMessage("Sorry, I'm having trouble connecting to my brain right now!", 'bot');
        }
    }

    addMessage(text, type) {
        const chatBody = document.getElementById('chat-body');
        if (!chatBody) return;

        const msg = document.createElement('div');
        msg.className = `rr-bubble rr-bubble-${type}`;
        msg.innerHTML = `
            ${text}
            <div class="rr-time">${new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</div>
        `;
        chatBody.appendChild(msg);
        chatBody.scrollTop = chatBody.scrollHeight;
    }

    showTyping(show) {
        this.isTyping = show;
        const chatBody = document.getElementById('chat-body');
        if (!chatBody) return;

        if (show) {
            const typing = document.createElement('div');
            typing.id = 'typing-indicator';
            typing.className = 'typing rr-bubble rr-bubble-bot';
            typing.innerHTML = '<div class="dot"></div><div class="dot"></div><div class="dot"></div>';
            chatBody.appendChild(typing);
        } else {
            const indicator = document.getElementById('typing-indicator');
            if (indicator) indicator.remove();
        }
        chatBody.scrollTop = chatBody.scrollHeight;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new RentRideChatbot();
});
