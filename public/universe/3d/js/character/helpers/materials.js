export function createSkinMaterial(color) {
    return new THREE.MeshPhysicalMaterial({
        color,
        roughness: 0.6,
        metalness: 0.1,
        clearcoat: 0.2
    });
}

export function createClothMaterial(color) {
    return new THREE.MeshStandardMaterial({
        color,
        roughness: 0.8
    });
}
