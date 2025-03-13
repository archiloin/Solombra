export function preloadImages(gameState, callback) {
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
