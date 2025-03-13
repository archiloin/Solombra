import * as THREE from "https://unpkg.com/three@0.112/build/three.module.js";

export function createTorso(config) {
    const geometry = new THREE.CylinderGeometry(config.width, config.width * 1.2, config.height, 32);
    const material = new THREE.MeshStandardMaterial({ color: config.color });
    const torso = new THREE.Mesh(geometry, material);
    torso.position.y = config.height / 2;
    return torso;
}

export const torsoConfig = {
    width: 0.5,
    height: 1.2,
    color: 0x00ff00
};
