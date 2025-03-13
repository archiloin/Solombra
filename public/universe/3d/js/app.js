import * as THREE from "https://unpkg.com/three@0.112/build/three.module.js";
import { createWorld } from './world.js';
import { createCharacter, animateCharacter } from './character.js';
import { switchToFirstPerson, switchToThirdPerson, updateThirdPersonCamera } from './camera.js';
import { checkEasterEgg, initEasterEgg } from './easterEgg.js';

let camera, scene, renderer, controls, character, clock;
let moveForward = false, moveBackward = false, moveLeft = false, moveRight = false;
let velocity = new THREE.Vector3();
let direction = new THREE.Vector3();
let isThirdPerson = false; // Par défaut, vue à la première personne
let easterEggElement;


document.addEventListener("DOMContentLoaded", () => {
    const toggleViewButton = document.getElementById("toggle-view");

    // Gestion du bouton pour basculer entre les vues
    toggleViewButton.addEventListener("click", toggleView);

    // Initialiser l'easter egg
    easterEggElement = initEasterEgg();

    // Appeler init après la configuration du bouton
    init();
    animate();
});

let isTouching = false;
let touchStartX = 0;
let touchStartY = 0;

function init() {
    // Initialisation de la scène et du rendu
    ({ scene, camera, renderer, controls } = createWorld());
    clock = new THREE.Clock();

    // Création du personnage
    character = createCharacter();
    scene.add(character);

    // Position initiale de la caméra pour la vue à la première personne
    switchToFirstPerson(camera, controls, character);

    // Gestion des événements
    document.addEventListener("keydown", onKeyDown);
    document.addEventListener("keyup", onKeyUp);
    window.addEventListener("resize", onWindowResize);

    // Gestion tactile pour les déplacements sur mobile
    document.addEventListener("touchstart", onTouchStart, false);
    document.addEventListener("touchmove", onTouchMove, false);
    document.addEventListener("touchend", onTouchEnd, false);

    // Activer PointerLock au clic (bureau uniquement)
    document.addEventListener("click", () => {
        if (!document.pointerLockElement) {
            controls.lock(); // Active PointerLock
        }

        // Passer en plein écran
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen();
        }
    });
}

function onTouchStart(event) {
    isTouching = true;
    touchStartX = event.touches[0].clientX;
    touchStartY = event.touches[0].clientY;
}

function onTouchMove(event) {
    if (!isTouching) return;

    const touch = event.touches[0];
    const deltaX = touch.clientX - touchStartX;
    const deltaY = touch.clientY - touchStartY;

    // Déplacement horizontal (rotation de la caméra ou du personnage)
    if (Math.abs(deltaX) > Math.abs(deltaY)) {
        if (deltaX > 0) {
            direction.x = 1; // Déplacement vers la droite
        } else {
            direction.x = -1; // Déplacement vers la gauche
        }
    }

    // Déplacement vertical (avant/arrière)
    if (Math.abs(deltaY) > Math.abs(deltaX)) {
        if (deltaY > 0) {
            moveBackward = true;
            moveForward = false;
        } else {
            moveForward = true;
            moveBackward = false;
        }
    }

    touchStartX = touch.clientX;
    touchStartY = touch.clientY;
}

function onTouchEnd() {
    isTouching = false;
    moveForward = false;
    moveBackward = false;
    direction.x = 0;
    direction.y = 0;
}


function animate() {
    requestAnimationFrame(animate);

    // Gestion des déplacements
    direction.z = Number(moveForward) - Number(moveBackward);
    direction.x = Number(moveRight) - Number(moveLeft);
    direction.normalize();

    const delta = clock.getDelta();
    const speed = 5.0;

    // Applique la vélocité au déplacement
    velocity.z -= direction.z * speed * delta;
    velocity.x -= direction.x * speed * delta;

    if (isThirdPerson) {
        // Déplacement du personnage en 3ème personne
        character.position.x += velocity.x * delta;
        character.position.z += velocity.z * delta;

        // Maintenir le personnage au niveau du sol
        character.position.y = 0; // Supposons que le sol est à y = 0

        // Rotation du personnage pour faire face à la direction de déplacement
        if (direction.length() > 0) {
            const angle = Math.atan2(direction.x, direction.z);
            character.rotation.y = angle;
        }

        // Mise à jour de la caméra pour suivre le personnage
        updateThirdPersonCamera(camera, character);
    } else {
        // Déplacement en vue à la première personne
        controls.moveRight(-velocity.x);
        controls.moveForward(-velocity.z);

        // Synchroniser la position du personnage avec celle de la caméra
        character.position.copy(controls.getObject().position);
        character.position.y = 0; // Assurez-vous que le personnage reste au sol
    }

    // Réduction progressive de la vélocité
    velocity.multiplyScalar(0.9);

    // Animation des membres uniquement lorsqu'il y a un mouvement
    if (velocity.length() > 0.1) {
        animateCharacter(character, clock.getElapsedTime());
    } else {
        resetCharacterPose();
    }

    // Vérifier l'easter egg
    checkEasterEgg(camera, easterEggElement);

    renderer.render(scene, camera);
}


function toggleView() {
    isThirdPerson = !isThirdPerson;
    if (isThirdPerson) {
        switchToThirdPerson(camera, character); // Passe à la 3ème personne
        controls.unlock(); // Désactive PointerLock
    } else {
        switchToFirstPerson(camera, controls, character); // Passe à la 1ère personne
    }
}

function resetCharacterPose() {
    // Réinitialise les rotations des membres
    character.children[2].rotation.z = 0; // Left arm
    character.children[3].rotation.z = 0; // Right arm
    character.children[4].rotation.x = 0; // Left leg
    character.children[5].rotation.x = 0; // Right leg
}

function onKeyDown(event) {
    switch (event.code) {
        case "ArrowUp":
        case "KeyW":
            moveForward = true;
            break;
        case "ArrowDown":
        case "KeyS":
            moveBackward = true;
            break;
        case "ArrowLeft":
        case "KeyA":
            moveLeft = true;
            break;
        case "ArrowRight":
        case "KeyD":
            moveRight = true;
            break;
    }
}

function onKeyUp(event) {
    switch (event.code) {
        case "ArrowUp":
        case "KeyW":
            moveForward = false;
            break;
        case "ArrowDown":
        case "KeyS":
            moveBackward = false;
            break;
        case "ArrowLeft":
        case "KeyA":
            moveLeft = false;
            break;
        case "ArrowRight":
        case "KeyD":
            moveRight = false;
            break;
    }
}

function onWindowResize() {
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(window.innerWidth, window.innerHeight);
}
