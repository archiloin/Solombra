export function setupInteractions(gameState) {
    // Configuration des interactions ici si nécessaire
}

export function checkInteractions(gameState) {
    const { character, objects } = gameState;

    objects.forEach((obj) => {
        if (obj.x === character.x && obj.y === character.y) {
            if (obj.type === 'sofa') {
                gameState.message = 'Vous vous asseyez sur le sofa. Confortable !';
            }
        }
    });
}
