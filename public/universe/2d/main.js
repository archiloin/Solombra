import { initializeCanvas, drawScene } from '/universe/2d/modules/draw.js';
import { setupMovement, moveCharacter } from '/universe/2d/modules/movement.js';
import { setupInteractions, checkInteractions } from '/universe/2d/modules/interaction.js';
import { setupChat, handleChatMessage } from '/universe/2d/modules/chat.js';
import { fetchRoomData, updatePlayer, sendMessage } from '/universe/2d/modules/ajax.js';

const ROOM_ID = 1; // Exemple : ID de la pièce
const PLAYER_ID = Math.floor(Math.random() * 10000); // Générer un ID unique pour le joueur
const PLAYER_NAME = `Player${PLAYER_ID}`; // Nom du joueur
const canvas = document.getElementById('gameCanvas');
const ctx = canvas.getContext('2d');

// Variables globales du jeu
const gameState = {
    players: {}, // Stocker tous les joueurs avec leurs positions
    character: { x: 0, y: 0, pixelX: 0, pixelY: 0 },
    objects: [
        { type: 'sofa', x: 15, y: 4, width: 180, height: 130 }, // Taille personnalisée
    ],
    tileSize: 40,
    characterSize: 200,
    message: '',
    images: {} // Espace pour stocker les images globales
};

// Charger l'image globale pour les joueurs
const playerImg = new Image();
playerImg.src = '/universe/2d/assets/personnage.png'; // Chemin de l'image
playerImg.onload = () => {
    console.log('Image du personnage chargée');
};

// Initialiser le joueur local dans la room
updatePlayer(ROOM_ID, PLAYER_ID, 0, 0, PLAYER_NAME);

// Ajouter un gestionnaire pour envoyer des messages
document.getElementById('sendMessageButton').addEventListener('click', () => {
    const messageInput = document.getElementById('messageInput');
    const message = messageInput.value.trim();
    if (message) {
        sendMessage(ROOM_ID, PLAYER_ID, message); // Envoyer le message au serveur
        addChatBubble(gameState, PLAYER_ID, message); // Afficher la bulle localement
        messageInput.value = ''; // Effacer le champ de saisie
    }
});


// Gestionnaire pour envoyer un message avec la touche "Entrée"
document.getElementById('messageInput').addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
        const messageInput = document.getElementById('messageInput');
        const message = messageInput.value.trim();
        if (message) {
            sendMessage(ROOM_ID, PLAYER_ID, message);
            messageInput.value = '';
        }
    }
});

// Déplacement du joueur local et mise à jour du serveur
canvas.addEventListener('click', (e) => {
    const rect = canvas.getBoundingClientRect();
    const x = Math.floor((e.clientX - rect.left) / gameState.tileSize);
    const y = Math.floor((e.clientY - rect.top) / gameState.tileSize);

    // Mettre à jour la position du joueur local dans gameState
    gameState.players[PLAYER_ID] = { x, y, name: PLAYER_NAME };

    // Redessiner tous les joueurs localement
    redrawPlayers(gameState);

    // Envoyer la mise à jour au serveur
    updatePlayer(ROOM_ID, PLAYER_ID, x, y, PLAYER_NAME);
});

// Fonction pour mettre à jour les joueurs
function updatePlayers(players, gameState) {
    players.forEach(player => {
        gameState.players[player.id] = {
            x: player.x,
            y: player.y,
            name: player.name,
        };
    });

    redrawPlayers(gameState); // Redessiner tous les joueurs
}

// Fonction pour redessiner les joueurs
function redrawPlayers(gameState) {
    const tileSize = gameState.tileSize;
    const characterSize = gameState.characterSize;

    // Nettoyer uniquement les zones des joueurs
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Dessiner chaque joueur
    Object.values(gameState.players).forEach(player => {
        ctx.drawImage(
            playerImg, // Image du joueur
            player.x * tileSize,
            player.y * tileSize,
            characterSize, // Largeur du personnage
            characterSize  // Hauteur du personnage
        );

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

// Initialisation des modules
preloadImages(gameState, () => {
    initializeCanvas(canvas, ctx, gameState);
    drawScene(ctx, gameState);
    setupMovement(canvas, gameState, moveCallback);
    setupInteractions(gameState);
    setupChat(handleChatCallback);

    // Rafraîchir les données toutes les 800 ms
    setInterval(() => fetchRoomData(ROOM_ID, gameState), 800);
});

function preloadImages(gameState, callback) {
    const imagesToLoad = {
        sofa: '/universe/2d/assets/objects/sofa.png',
        player: '/universe/2d/assets/personnage.png',
    };

    const loadedImages = {};
    let loadedCount = 0;
    const totalImages = Object.keys(imagesToLoad).length;

    Object.entries(imagesToLoad).forEach(([key, src]) => {
        const img = new Image();
        img.src = src;
        img.onload = () => {
            loadedImages[key] = img;
            loadedCount++;
            if (loadedCount === totalImages) {
                gameState.images = loadedImages;
                callback();
            }
        };
    });
}

// Callback pour déplacer le personnage et vérifier les interactions
function moveCallback() {
    checkInteractions(gameState); // Vérifie les interactions après un déplacement
    drawScene(ctx, gameState); // Redessine la scène
}

// Callback pour gérer un message de chat
function handleChatCallback(message) {
    const MESSAGE_DISPLAY_TIME = 6000; // Durée d'affichage en millisecondes

    // Stocke le message dans l'état du jeu
    gameState.message = message;
    drawScene(ctx, gameState); // Redessine la scène pour afficher la bulle

    // Efface le message après 6 secondes
    setTimeout(() => {
        gameState.message = ''; // Supprime le message
        drawScene(ctx, gameState); // Redessine pour effacer la bulle
    }, MESSAGE_DISPLAY_TIME);
}

// Dessiner la scène initiale
drawScene(ctx, gameState);
