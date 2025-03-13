export function initializeCanvas(canvas, ctx, gameState) {
    canvas.width = 960;
    canvas.height = 640;
    ctx.clearRect(0, 0, canvas.width, canvas.height);
}

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

export function drawScene(ctx, gameState) {
    const { character, objects, tileSize, characterSize, message, images } = gameState;

    // Nettoyer le canvas
    ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);

    // Dessiner les objets
    objects.forEach((obj) => {
        const pixelX = obj.x * tileSize;
        const pixelY = obj.y * tileSize;

        if (obj.type === 'sofa' && images.sofa) {
            const sofaWidth = obj.width || tileSize * 3; // Largeur définie ou valeur par défaut
            const sofaHeight = obj.height || tileSize * 1.5; // Hauteur définie ou valeur par défaut
            ctx.drawImage(images.sofa, pixelX, pixelY, sofaWidth, sofaHeight);
        }
    });

    // Dessiner le personnage
    if (images.player) {
        ctx.drawImage(
            images.player,
            character.pixelX,
            character.pixelY,
            characterSize,
            characterSize
        );
    }

    // Dessiner la bulle de message
    if (message) {
        drawMessageBubble(ctx, character.pixelX + characterSize / 2, character.pixelY, message);
    }
}

function drawMessageBubble(ctx, x, y, text) {
    const padding = 10;
    const fontSize = 14;

    ctx.font = `${fontSize}px Arial`;
    const textWidth = ctx.measureText(text).width;

    const bubbleWidth = textWidth + padding * 2;
    const bubbleHeight = fontSize + padding * 2;

    const bubbleX = x - bubbleWidth / 2;
    const bubbleY = y - bubbleHeight - 10;

    // Dessiner la bulle
    ctx.fillStyle = 'white';
    ctx.strokeStyle = 'black';
    ctx.lineWidth = 2;
    ctx.beginPath();
    ctx.roundRect(bubbleX, bubbleY, bubbleWidth, bubbleHeight, 10);
    ctx.fill();
    ctx.stroke();

    // Dessiner le texte
    ctx.fillStyle = 'black';
    ctx.fillText(
        text,
        bubbleX + padding,
        bubbleY + bubbleHeight / 2 + fontSize / 2 - padding
    );
}
