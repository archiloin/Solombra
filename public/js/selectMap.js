document.addEventListener("DOMContentLoaded", function() {
    var svgOverlay = document.getElementById('svg-overlay');
    
    function createSvgPolygon(coords) {
        var svgns = "http://www.w3.org/2000/svg";
        var polygon = document.createElementNS(svgns, 'polygon');
        polygon.setAttributeNS(null, 'points', coords);
        polygon.setAttributeNS(null, 'style', 'fill: #0A0A23; fill-opacity: 0;');
        svgOverlay.appendChild(polygon);
        return polygon;
    }

    document.querySelectorAll('area').forEach(function(area) {
        var coords = area.getAttribute('data-original-coords').split(',').join(' ');
        var polygon = createSvgPolygon(coords);
        
        area.addEventListener('mouseenter', function() {
            polygon.style.fillOpacity = '0.33';
        });
        area.addEventListener('mouseleave', function() {
            polygon.style.fillOpacity = '0';
        });
    });
});