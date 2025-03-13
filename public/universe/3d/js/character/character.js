import * as THREE from "https://unpkg.com/three@0.112/build/three.module.js";
import * as dat from "https://cdn.jsdelivr.net/npm/dat.gui/build/dat.gui.module.js";

export function createCharacter(config) {
    const character = new THREE.Group();

    // Créer le corps
    const body = createBody(config.body);
    character.add(body);

    // Créer la tête
    const head = createHead(config.head);
    head.position.y = config.body.height + config.head.radius;
    character.add(head);

    // Créer les bras avec articulations
    const leftShoulder = createJoint(0.1, 0x000000);
    leftShoulder.position.set(-config.body.width / 2 - 0.1, config.body.height * 0.75, 0);
    character.add(leftShoulder);

    const leftArm = createArm(config.arms.left);
    leftArm.position.set(0, -config.arms.left.length / 2, 0);
    leftShoulder.add(leftArm);

    const rightShoulder = createJoint(0.1, 0x000000);
    rightShoulder.position.set(config.body.width / 2 + 0.1, config.body.height * 0.75, 0);
    character.add(rightShoulder);

    const rightArm = createArm(config.arms.right);
    rightArm.position.set(0, -config.arms.right.length / 2, 0);
    rightShoulder.add(rightArm);

    // Créer les jambes avec articulations
    const leftHip = createJoint(0.15, 0x000000);
    leftHip.position.set(-config.body.width / 4, 0, 0);
    character.add(leftHip);

    const leftLeg = createLeg(config.legs.left);
    leftLeg.position.set(0, -config.legs.left.length / 2, 0);
    leftHip.add(leftLeg);

    const rightHip = createJoint(0.15, 0x000000);
    rightHip.position.set(config.body.width / 4, 0, 0);
    character.add(rightHip);

    const rightLeg = createLeg(config.legs.right);
    rightLeg.position.set(0, -config.legs.right.length / 2, 0);
    rightHip.add(rightLeg);

    // Ajouter les accessoires
    if (config.accessories) {
        for (const accessoryConfig of config.accessories) {
            const accessory = addAccessory(accessoryConfig);
            character.add(accessory);
        }
    }

    return character;
}

function createBody(config) {
    const geometry = new THREE.CylinderGeometry(config.width, config.width * 1.2, config.height, 32);
    const material = new THREE.MeshPhysicalMaterial({
        color: config.color,
        roughness: 0.6,
        metalness: 0.2,
        clearcoat: 0.3,
    });
    const body = new THREE.Mesh(geometry, material);
    body.position.y = config.height / 2;
    return body;
}

function createHead(config) {
    const geometry = new THREE.SphereGeometry(config.radius, 32, 32);
    const material = new THREE.MeshStandardMaterial({ color: config.color });
    const head = new THREE.Mesh(geometry, material);
    return head;
}

function createArm(config) {
    const geometry = new THREE.CylinderGeometry(config.width, config.width, config.length, 16);
    const material = new THREE.MeshStandardMaterial({ color: config.color });
    const arm = new THREE.Mesh(geometry, material);
    return arm;
}

function createLeg(config) {
    const geometry = new THREE.CylinderGeometry(config.width, config.width, config.length, 16);
    const material = new THREE.MeshStandardMaterial({ color: config.color });
    const leg = new THREE.Mesh(geometry, material);
    return leg;
}

function createJoint(radius, color) {
    const geometry = new THREE.SphereGeometry(radius, 16, 16);
    const material = new THREE.MeshStandardMaterial({ color: color });
    const joint = new THREE.Mesh(geometry, material);
    return joint;
}

function addAccessory(config) {
    let accessory;
    switch (config.type) {
        case "hat":
            accessory = new THREE.Mesh(
                new THREE.CylinderGeometry(0.3, 0.3, 0.5, 32),
                new THREE.MeshStandardMaterial({ color: config.color })
            );
            accessory.position.y = config.position.y;
            break;
        case "glasses":
            // Ajouter une implémentation pour les lunettes
            break;
    }
    return accessory;
}

export function animateCharacter(character, elapsedTime, animationConfig) {
    const { armSpeed, legSpeed, armAmplitude, legAmplitude } = animationConfig;

    // Mouvement des bras
    character.children[2].children[0].rotation.z = Math.sin(elapsedTime * armSpeed) * armAmplitude; // Left arm
    character.children[3].children[0].rotation.z = -Math.sin(elapsedTime * armSpeed) * armAmplitude; // Right arm

    // Mouvement des jambes
    character.children[4].children[0].rotation.x = Math.sin(elapsedTime * legSpeed) * legAmplitude; // Left leg
    character.children[5].children[0].rotation.x = -Math.sin(elapsedTime * legSpeed) * legAmplitude; // Right leg
}

export function setupDatGUI(character, config, updateCharacter) {
    const gui = new dat.GUI();

    // Corps
    const bodyFolder = gui.addFolder("Body");
    bodyFolder.add(config.body, "width", 0.1, 2).onChange(() => updateCharacter(character, config));
    bodyFolder.add(config.body, "height", 0.1, 3).onChange(() => updateCharacter(character, config));
    bodyFolder.addColor(config.body, "color").onChange(() => updateCharacter(character, config));

    // Tête
    const headFolder = gui.addFolder("Head");
    headFolder.add(config.head, "radius", 0.1, 1).onChange(() => updateCharacter(character, config));
    headFolder.addColor(config.head, "color").onChange(() => updateCharacter(character, config));

    // Bras
    const armsFolder = gui.addFolder("Arms");
    const leftArmFolder = armsFolder.addFolder("Left Arm");
    leftArmFolder.add(config.arms.left, "length", 0.1, 2).onChange(() => updateCharacter(character, config));
    leftArmFolder.add(config.arms.left, "width", 0.1, 1).onChange(() => updateCharacter(character, config));
    leftArmFolder.addColor(config.arms.left, "color").onChange(() => updateCharacter(character, config));

    const rightArmFolder = armsFolder.addFolder("Right Arm");
    rightArmFolder.add(config.arms.right, "length", 0.1, 2).onChange(() => updateCharacter(character, config));
    rightArmFolder.add(config.arms.right, "width", 0.1, 1).onChange(() => updateCharacter(character, config));
    rightArmFolder.addColor(config.arms.right, "color").onChange(() => updateCharacter(character, config));

    // Jambes
    const legsFolder = gui.addFolder("Legs");
    const leftLegFolder = legsFolder.addFolder("Left Leg");
    leftLegFolder.add(config.legs.left, "length", 0.1, 2).onChange(() => updateCharacter(character, config));
    leftLegFolder.add(config.legs.left, "width", 0.1, 1).onChange(() => updateCharacter(character, config));
    leftLegFolder.addColor(config.legs.left, "color").onChange(() => updateCharacter(character, config));

    const rightLegFolder = legsFolder.addFolder("Right Leg");
    rightLegFolder.add(config.legs.right, "length", 0.1, 2).onChange(() => updateCharacter(character, config));
    rightLegFolder.add(config.legs.right, "width", 0.1, 1).onChange(() => updateCharacter(character, config));
    rightLegFolder.addColor(config.legs.right, "color").onChange(() => updateCharacter(character, config));

    // Accessoires
    const accessoriesFolder = gui.addFolder("Accessories");
    for (let i = 0; i < config.accessories.length; i++) {
        const accessoryConfig = config.accessories[i];
        const accessoryFolder = accessoriesFolder.addFolder(`Accessory ${i + 1}`);
        accessoryFolder.addColor(accessoryConfig, "color").onChange(() => updateCharacter(character, config));
    }
}
