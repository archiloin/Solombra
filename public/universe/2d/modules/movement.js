export function setupMovement(canvas, gameState, moveCallback) {
    canvas.addEventListener('click', (e) => {
        const rect = canvas.getBoundingClientRect();
        const mouseX = e.clientX - rect.left;
        const mouseY = e.clientY - rect.top;

        // Calculer la case cliquée
        const clickedCol = Math.floor(mouseX / gameState.tileSize);
        const clickedRow = Math.floor(mouseY / gameState.tileSize);

        // Calculer la position en pixels cible
        const targetX = clickedCol * gameState.tileSize;
        const targetY = clickedRow * gameState.tileSize;

        // Déplacer le personnage
        moveCharacter(gameState, targetX, targetY, moveCallback);
    });
}

export function moveCharacter(gameState, targetX, targetY, callback) {
    const startX = gameState.character.pixelX;
    const startY = gameState.character.pixelY;
    const deltaX = targetX - startX;
    const deltaY = targetY - startY;
    const duration = 1500; // Durée totale du déplacement (1,5 seconde)
    const startTime = performance.now();

    function step(currentTime) {
        const elapsedTime = currentTime - startTime;
        const progress = Math.min(elapsedTime / duration, 1); // Progression de 0 à 1

        // Mise à jour progressive des positions
        gameState.character.pixelX = startX + deltaX * progress;
        gameState.character.pixelY = startY + deltaY * progress;

        // Redessiner la scène à chaque frame
        callback();

        // Continuer l'animation tant que le déplacement n'est pas terminé
        if (progress < 1) {
            requestAnimationFrame(step);
        } else {
            // Une fois terminé, mettre à jour les coordonnées logiques (en cases)
            gameState.character.x = targetX / gameState.tileSize;
            gameState.character.y = targetY / gameState.tileSize;
        }
    }

    requestAnimationFrame(step);
}

