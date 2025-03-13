import * as THREE from "https://unpkg.com/three@0.112/build/three.module.js";

export function createEyes(config) {
    const eyeGroup = new THREE.Group();

    // Iris
    const irisGeometry = new THREE.SphereGeometry(config.iris.size, 16, 16);
    const irisMaterial = new THREE.MeshStandardMaterial({ color: config.iris.color });
    const iris = new THREE.Mesh(irisGeometry, irisMaterial);
    iris.position.z = config.size * 0.9;
    eyeGroup.add(iris);

    // Pupils
    const pupilGeometry = new THREE.SphereGeometry(config.pupil.size, 16, 16);
    const pupilMaterial = new THREE.MeshStandardMaterial({ color: config.pupil.color });
    const pupil = new THREE.Mesh(pupilGeometry, pupilMaterial);
    pupil.position.z = config.size * 0.95;
    eyeGroup.add(pupil);

    return eyeGroup;
}
