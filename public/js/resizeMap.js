function resizeMap() {
    var image = document.querySelector('.image-map');
    var svgOverlay = document.getElementById('svg-overlay');
    var mapName = image.getAttribute('usemap').replace('#', '');
    var areas = document.querySelectorAll('map[name="' + mapName + '"] > area');
    var polygons = svgOverlay.querySelectorAll('polygon');

    areas.forEach(function(area, index) {
        var originalCoords = area.dataset.originalCoords.split(',');
        var adjustedCoords = originalCoords.map(function(coord, index) {
            // Ajuster les coordonnées en utilisant la largeur de l'image comme référence
            return Math.round(coord * (image.clientWidth / image.naturalWidth));
        });
        area.coords = adjustedCoords.join(',');

        // Ajuster également les points du polygone SVG correspondant
        if (polygons[index]) {
            polygons[index].setAttribute('points', adjustedCoords.join(' '));
        }
    });
}

// Attacher l'événement resize et l'appel initial au chargement de la fenêtre
window.addEventListener('resize', resizeMap);
window.addEventListener('load', resizeMap);
