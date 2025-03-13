import * as THREE from "https://unpkg.com/three@0.112/build/three.module.js";

export function createNose(config) {
    const noseGeometry = new THREE.ConeGeometry(config.radius, config.height, 16);
    const noseMaterial = new THREE.MeshStandardMaterial({ color: config.color });
    const nose = new THREE.Mesh(noseGeometry, noseMaterial);
    nose.rotation.x = Math.PI / 2;
    return nose;
}
