// Exemple de contenu du fichier chat.js

// Fonction pour gérer un message de chat
export function handleChatMessage(message) {
    console.log('Message reçu :', message);
    // Autres actions basées sur le message
}

// Autres exports éventuels
export function setupChat(callback) {
    const messageInput = document.getElementById('messageInput');
    const sendMessageButton = document.getElementById('sendMessageButton');

    sendMessageButton.addEventListener('click', () => {
        const message = messageInput.value.trim();
        if (message) {
            callback(message);
            messageInput.value = '';
        }
    });

    messageInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            const message = messageInput.value.trim();
            if (message) {
                callback(message);
                messageInput.value = '';
            }
        }
    });
}
