import * as THREE from "https://unpkg.com/three@0.112/build/three.module.js";
import * as dat from "https://cdn.jsdelivr.net/npm/dat.gui/build/dat.gui.module.js";

import { createCharacter, setupDatGUI } from "./index.js";

export const characterConfig = {
    body: { width: 0.5, height: 1, depth: 0.5, color: 0x00ff00 },
    head: { 
        radius: 0.3, 
        color: 0x00ff88,
        eyes: { iris: { size: 0.05, color: 0x0000ff }, pupil: { size: 0.02, color: 0x000000 } },
        mouth: { lips: { radius: 0.3, depth: 0.05, color: 0xff0000 } },
        nose: { radius: 0.05, height: 0.2, color: 0xffd1a4 },
        ears: { size: 0.15, offset: 0.6, color: 0xffe0bd },
        hair: { size: 1.05, offset: 0.2, color: 0x000000 }
    },
    arms: {
        left: { length: 0.6, width: 0.2, depth: 0.2, color: 0x00ff00 },
        right: { length: 0.6, width: 0.2, depth: 0.2, color: 0x00ff00 }
    },
    legs: {
        left: { length: 0.8, width: 0.2, depth: 0.2, color: 0x00ff00 },
        right: { length: 0.8, width: 0.2, depth: 0.2, color: 0x00ff00 }
    },
    accessories: [
        { type: "hat", color: 0x000000, position: { y: 2.2 } }
    ]
};

// Créer la scène et ajouter le personnage
const scene = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
camera.position.z = 5;

const renderer = new THREE.WebGLRenderer();
renderer.setSize(window.innerWidth, window.innerHeight);
document.body.appendChild(renderer.domElement);

const light = new THREE.DirectionalLight(0xffffff, 1);
light.position.set(5, 5, 5);
scene.add(light);

let character = createCharacter(characterConfig);
scene.add(character);

// Mise à jour du personnage
function updateCharacter(config) {
    scene.remove(character);
    character = createCharacter(config);
    scene.add(character);
}

// Configuration de dat.GUI
setupDatGUI(characterConfig, updateCharacter);

// Animation
function animate() {
    requestAnimationFrame(animate);
    renderer.render(scene, camera);
}
animate();
