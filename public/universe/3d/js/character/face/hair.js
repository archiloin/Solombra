import * as THREE from "https://unpkg.com/three@0.112/build/three.module.js";

export function createHair(config) {
    const hairGeometry = new THREE.SphereGeometry(config.size, 32, 32, 0, Math.PI);
    const hairMaterial = new THREE.MeshStandardMaterial({ color: config.color });
    const hair = new THREE.Mesh(hairGeometry, hairMaterial);
    hair.position.y = config.offset;
    return hair;
}
