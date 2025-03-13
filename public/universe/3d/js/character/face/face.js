import * as THREE from "https://unpkg.com/three@0.112/build/three.module.js";
import { createEyes } from './eyes.js';
import { createMouth } from './mouth.js';
import { createNose } from './nose.js';
import { createEars } from './ears.js';
import { createHair } from './hair.js';

export function createFace(config) {
    const faceGroup = new THREE.Group();

    const eyes = createEyes(config.eyes);
    const mouth = createMouth(config.mouth);
    const nose = createNose(config.nose);
    const ears = createEars(config.ears);
    const hair = createHair(config.hair);

    faceGroup.add(eyes, mouth, nose, ears, hair);
    return faceGroup;
}

// Configuration par défaut pour le visage
export const faceConfig = {
    size: 1, // Taille de la tête
    eyes: {
        iris: { size: 0.05, color: 0x0000ff },
        pupil: { size: 0.02, color: 0x000000 }
    },
    mouth: {
        lips: { radius: 0.3, depth: 0.05, color: 0xff0000 },
        teeth: { width: 0.2, height: 0.05, depth: 0.05, color: 0xffffff }
    },
    nose: { radius: 0.05, height: 0.2, color: 0xffd1a4 },
    ears: { size: 0.15, offset: 0.6, color: 0xffe0bd },
    hair: { size: 1.05, offset: 0.2, color: 0x000000 }
};
