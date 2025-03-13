import * as THREE from "https://unpkg.com/three@0.112/build/three.module.js";

export function switchToFirstPerson(camera, controls, character) {
    // Sur mobile, ne pas activer PointerLock
    if (isMobile()) {
        camera.position.set(character.position.x, character.position.y + 1.8, character.position.z);
    } else {
        controls.lock(); // Bureau uniquement
        controls.getObject().position.set(character.position.x, character.position.y + 1.8, character.position.z);
    }
}

export function switchToThirdPerson(camera, character) {
    const offset = new THREE.Vector3(0, 2, -5); // Position de la caméra relative au personnage
    camera.position.copy(character.position.clone().add(offset));
    camera.position.y = 2; // Hauteur de la caméra par rapport au sol
    camera.lookAt(character.position); // La caméra regarde toujours le personnage
}


export function updateThirdPersonCamera(camera, character) {
    const offset = new THREE.Vector3(0, 2, -5); // Position relative de la caméra
    const targetPosition = character.position.clone().add(offset);

    // Interpolation pour un déplacement fluide
    camera.position.lerp(targetPosition, 0.1);
    camera.lookAt(character.position);
}

// Vérification si mobile
function isMobile() {
    return /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
}