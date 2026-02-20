document.addEventListener("DOMContentLoaded", function () {

    const chatBubble = document.getElementById("chat-bubble");
    const chatWindow = document.getElementById("chat-window");
    const closeChat = document.getElementById("close-chat");

    const chatMessages = document.getElementById("chat-messages");
    const userInput = document.getElementById("user-input");
    const sendBtn = document.getElementById("send-btn");

    if (!chatBubble || !chatWindow || !closeChat) {
        console.error("Chatbot elements not found. Check IDs.");
        return;
    }

    chatBubble.addEventListener("click", function () {
        chatWindow.classList.add("active");
        chatBubble.style.display = "none";
    });

    closeChat.addEventListener("click", function () {
        chatWindow.classList.remove("active");
        chatBubble.style.display = "flex";
    });

    sendBtn.addEventListener("click", sendMessage);

    userInput.addEventListener("keydown", function (e) {
        if (e.key === "Enter") {
            e.preventDefault();
            sendMessage();
        }
    });

    function appendMessage(text, type) {
        const div = document.createElement("div");
        div.classList.add("message", type);
        div.textContent = text;
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function sendMessage() {
        const userMessage = userInput.value.trim();
        if (userMessage === "") return;

        appendMessage(userMessage, "user");
        userInput.value = "";

        fetch("chatbot/chatbot_backend.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ message: userMessage })
        })
            .then(res => res.json())
            .then(data => {
                appendMessage(data.reply || "No reply.", "bot");
            })
            .catch(() => {
                appendMessage("Chatbot service unavailable.", "bot");
            });
    }

});
