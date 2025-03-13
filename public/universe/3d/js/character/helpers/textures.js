import * as THREE from "https://unpkg.com/three@0.112/build/three.module.js";

export function loadTexture(url) {
    const loader = new THREE.TextureLoader();
    return loader.load(url);
}

export function applySkinTexture(mesh, url) {
    const texture = loadTexture(url);
    mesh.material.map = texture;
    mesh.material.needsUpdate = true;
}
