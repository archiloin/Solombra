import * as THREE from "https://unpkg.com/three@0.112/build/three.module.js";

export function createEars(config) {
    const earsGroup = new THREE.Group();

    // Oreille gauche
    const leftEarGeometry = new THREE.SphereGeometry(config.size, 16, 16);
    const leftEarMaterial = new THREE.MeshStandardMaterial({ color: config.color });
    const leftEar = new THREE.Mesh(leftEarGeometry, leftEarMaterial);
    leftEar.position.set(-config.offset, 0, 0);
    earsGroup.add(leftEar);

    // Oreille droite
    const rightEarGeometry = new THREE.SphereGeometry(config.size, 16, 16);
    const rightEarMaterial = new THREE.MeshStandardMaterial({ color: config.color });
    const rightEar = new THREE.Mesh(rightEarGeometry, rightEarMaterial);
    rightEar.position.set(config.offset, 0, 0);
    earsGroup.add(rightEar);

    return earsGroup;
}
