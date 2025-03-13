import * as THREE from "https://unpkg.com/three@0.112/build/three.module.js";
import { PointerLockControls } from "https://unpkg.com/three@0.112/examples/jsm/controls/PointerLockControls.js";

export function createWorld() {
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
    const renderer = new THREE.WebGLRenderer({ antialias: true });
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setPixelRatio(window.devicePixelRatio);
    document.body.appendChild(renderer.domElement);

    const controls = new PointerLockControls(camera, document.body);
    document.addEventListener("click", () => controls.lock());

    // Lumière et sphère panoramique
    scene.add(new THREE.AmbientLight(0xffffff, 0.8));
    const sphereGeometry = new THREE.SphereGeometry(500, 60, 40);
    const sphereMaterial = new THREE.MeshBasicMaterial({ side: THREE.BackSide });
    const textureLoader = new THREE.TextureLoader();
    textureLoader.load(
        "/universe/3d/images/3.webp",
        (texture) => {
            sphereMaterial.map = texture;
            sphereMaterial.needsUpdate = true;
        }
    );
    const sphere = new THREE.Mesh(sphereGeometry, sphereMaterial);
    scene.add(sphere);


    return { scene, camera, renderer, controls };
}
