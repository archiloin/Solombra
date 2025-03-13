export function fetchRoomData(roomId, gameState) {
    fetch(`/member/universe/room/${roomId}`)
        .then(response => response.json())
        .then(data => {
            updatePlayers(data.players, gameState); // Met à jour les joueurs
            updateMessages(data.messages, gameState); // Met à jour les messages
        })
        .catch(error => console.error('Erreur lors de la récupération des données :', error));
}


// Mettre à jour les joueurs
function updatePlayers(players, gameState) {
    // Mettre à jour les informations des joueurs dans gameState
    players.forEach(player => {
        gameState.players[player.id] = {
            x: player.x,
            y: player.y,
            name: player.name,
        };
    });

    // Redessiner la scène complète (objets + joueurs)
    redrawScene(gameState);
}

// Redessiner la scène complète
function redrawScene(gameState) {
    const canvas = document.getElementById('gameCanvas');
    const ctx = canvas.getContext('2d');
    const tileSize = gameState.tileSize;
    const characterSize = gameState.characterSize;

    // Nettoyer le canvas entier
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Dessiner les objets statiques (comme le sofa)
    gameState.objects.forEach(obj => {
        if (obj.type === 'sofa') {
            // Utiliser les dimensions personnalisées définies dans gameState
            const objWidth = obj.width || tileSize * 2; // Largeur par défaut si non définie
            const objHeight = obj.height || tileSize;  // Hauteur par défaut si non définie

            if (gameState.images.sofa) {
                ctx.drawImage(
                    gameState.images.sofa,
                    obj.x * tileSize,
                    obj.y * tileSize,
                    objWidth,
                    objHeight
                );
            }
        } else {
            ctx.fillStyle = 'gray'; // Couleur par défaut pour d'autres objets
            ctx.fillRect(
                obj.x * tileSize,
                obj.y * tileSize,
                obj.width || tileSize,  // Largeur par défaut si non définie
                obj.height || tileSize  // Hauteur par défaut si non définie
            );
        }
    });

    // Dessiner chaque joueur
    Object.values(gameState.players).forEach(player => {
        const playerImg = new Image();
        playerImg.src = '/universe/2d/assets/personnage.png'; // Chemin vers l'image du joueur
        playerImg.onload = () => {
            ctx.drawImage(
                playerImg,
                player.x * tileSize,
                player.y * tileSize,
                characterSize,
                characterSize
            );
        };

        // Ajouter le nom du joueur
        ctx.font = '14px Arial';
        ctx.fillStyle = 'white';
        ctx.fillText(
            player.name,
            player.x * tileSize,
            player.y * tileSize - 10
        );
    });
}

// Fonction pour gérer les messages
function updateMessages(messages) {
    const chatMessages = document.getElementById('chat-messages');

    // Ajouter uniquement les nouveaux messages
    messages.forEach(msg => {
        const existingMessages = Array.from(chatMessages.children).map(
            el => el.textContent
        );
        const newMessage = `Player ${msg.player_id}: ${msg.message}`;
        if (!existingMessages.includes(newMessage)) {
            const msgElement = document.createElement('div');
            msgElement.textContent = newMessage;
            //chatMessages.prepend(msgElement); Insère le message au début ou 
            chatMessages.appendChild(msgElement);
        }
    });
}


// Ajouter ou mettre à jour un joueur
export function updatePlayer(roomId, playerId, x, y, name = 'Player') {
    fetch(`/member/universe/room/${roomId}/player`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: playerId, x, y, name }),
    })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                console.error('Erreur lors de la mise à jour du joueur');
            }
        })
        .catch(error => console.error('Erreur lors de la mise à jour du joueur :', error));
}

// Envoyer un message
export function sendMessage(roomId, playerId, message) {
    fetch(`/member/universe/room/${roomId}/message`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ player_id: playerId, message }),
    })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                console.error('Erreur lors de l\'envoi du message');
            }
        })
        .catch(error => console.error('Erreur lors de l\'envoi du message :', error));
}
