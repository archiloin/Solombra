export function checkEasterEgg(camera, easterEggElement) {
    // Vérifier si la caméra est suffisamment loin pour déclencher l'easter egg
    if (camera.position.length() > 1350) {
        easterEggElement.style.display = "block"; // Afficher le texte
    } else {
        easterEggElement.style.display = "none"; // Masquer le texte
    }
}

export function initEasterEgg() {
    const easterEggElement = document.createElement("div");
    easterEggElement.id = "easter-egg";
    easterEggElement.innerHTML = "<h1>Solombra</h1>";
    easterEggElement.style.cssText = `
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 24px;
        font-family: Arial, sans-serif;
        text-shadow: 0px 0px 10px rgba(255, 255, 255, 0.8);
        display: none; /* Masqué par défaut */
    `;
    document.body.appendChild(easterEggElement);
    return easterEggElement;
}