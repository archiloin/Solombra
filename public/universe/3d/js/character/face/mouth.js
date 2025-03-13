import * as THREE from "https://unpkg.com/three@0.112/build/three.module.js";

export function createMouth(config) {
    const mouthGroup = new THREE.Group();

    // Lèvres
    const lipsGeometry = new THREE.CylinderGeometry(config.lips.radius, config.lips.radius, config.lips.depth, 32, 1, true, Math.PI, Math.PI);
    const lipsMaterial = new THREE.MeshStandardMaterial({ color: config.lips.color });
    const lips = new THREE.Mesh(lipsGeometry, lipsMaterial);
    lips.rotation.z = Math.PI;
    mouthGroup.add(lips);

    // Dents
    if (config.teeth) {
        const teethGeometry = new THREE.BoxGeometry(config.teeth.width, config.teeth.height, config.teeth.depth);
        const teethMaterial = new THREE.MeshStandardMaterial({ color: config.teeth.color });
        const teeth = new THREE.Mesh(teethGeometry, teethMaterial);
        teeth.position.y = -config.lips.radius / 2;
        mouthGroup.add(teeth);
    }

    return mouthGroup;
}
