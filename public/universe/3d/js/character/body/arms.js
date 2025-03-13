import * as THREE from "https://unpkg.com/three@0.112/build/three.module.js";
export function createArms(config) {
    const armsGroup = new THREE.Group();

    // Bras gauche
    const leftArm = new THREE.Mesh(
        new THREE.CylinderGeometry(config.width, config.width, config.length, 16),
        new THREE.MeshStandardMaterial({ color: config.color })
    );
    leftArm.position.set(-config.offset, config.torsoHeight * 0.75, 0);
    armsGroup.add(leftArm);

    // Bras droit
    const rightArm = new THREE.Mesh(
        new THREE.CylinderGeometry(config.width, config.width, config.length, 16),
        new THREE.MeshStandardMaterial({ color: config.color })
    );
    rightArm.position.set(config.offset, config.torsoHeight * 0.75, 0);
    armsGroup.add(rightArm);

    return armsGroup;
}

export const armsConfig = {
    width: 0.15,
    length: 0.8,
    offset: 0.6,
    torsoHeight: 1.2,
    color: 0x00ff00
};
