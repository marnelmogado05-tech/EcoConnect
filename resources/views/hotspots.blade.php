<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incident Hotspot Map</title>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Marker Clustering CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
    
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        #map {
            height: 100vh;
            width: 100%;
        }
        
        .header {
            position: absolute;
            top: 10px;
            left: 50px;
            right: 50px;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.9);
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            margin: 0;
            font-size: 1.5rem;
            color: #333;
        }
        
        .controls {
            display: flex;
            gap: 15px;
        }
        
        select, button {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
            font-size: 0.9rem;
        }
        
        button {
            background: #3498db;
            color: white;
            border: none;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        button:hover {
            background: #2980b9;
        }
        
        .legend {
            position: absolute;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.9);
            padding: 10px 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            margin: 5px 0;
        }
        
        .legend-color {
            width: 20px;
            height: 20px;
            margin-right: 8px;
            border-radius: 50%;
        }
        
        .incident-popup {
            max-width: 250px;
        }
        
        .incident-popup h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        
        .incident-popup p {
            margin: 5px 0;
        }
        
        .incident-popup .severity {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            color: white;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .severity-low {
            background-color: #2ecc71;
        }
        
        .severity-medium {
            background-color: #f39c12;
        }
        
        .severity-high {
            background-color: #e74c3c;
        }
        
        .cluster-low {
            background-color: rgba(46, 204, 113, 0.7);
            border: 3px solid rgba(46, 204, 113, 0.9);
        }
        
        .cluster-medium {
            background-color: rgba(243, 156, 18, 0.7);
            border: 3px solid rgba(243, 156, 18, 0.9);
        }
        
        .cluster-high {
            background-color: rgba(231, 76, 60, 0.7);
            border: 3px solid rgba(231, 76, 60, 0.9);
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Incident Hotspot Map</h1>
        <div class="controls">
            <select id="incidentType">
                <option value="all">All Incident Types</option>
                <option value="theft">Theft</option>
                <option value="vandalism">Vandalism</option>
                <option value="assault">Assault</option>
                <option value="accident">Accident</option>
            </select>
            <select id="severityFilter">
                <option value="all">All Severity Levels</option>
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
            </select>
            <button id="resetView">Reset View</button>
        </div>
    </div>
    
    <div id="map"></div>
    
    <div class="legend">
        <h3>Severity Levels</h3>
        <div class="legend-item">
            <div class="legend-color" style="background-color: #2ecc71;"></div>
            <span>Low Severity</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background-color: #f39c12;"></div>
            <span>Medium Severity</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background-color: #e74c3c;"></div>
            <span>High Severity</span>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- Marker Clustering Plugin -->
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    
    <script>
        // Initialize the map
        const map = L.map('map').setView([40.7128, -74.0060], 12); // Default to New York City
        
        // Add tile layer (OpenStreetMap)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // Create marker clusters for different severity levels
        const lowCluster = L.markerClusterGroup({
            iconCreateFunction: function(cluster) {
                return L.divIcon({
                    html: '<div class="cluster-low">' + cluster.getChildCount() + '</div>',
                    className: 'marker-cluster-custom',
                    iconSize: L.point(40, 40)
                });
            }
        });
        
        const mediumCluster = L.markerClusterGroup({
            iconCreateFunction: function(cluster) {
                return L.divIcon({
                    html: '<div class="cluster-medium">' + cluster.getChildCount() + '</div>',
                    className: 'marker-cluster-custom',
                    iconSize: L.point(40, 40)
                });
            }
        });
        
        const highCluster = L.markerClusterGroup({
            iconCreateFunction: function(cluster) {
                return L.divIcon({
                    html: '<div class="cluster-high">' + cluster.getChildCount() + '</div>',
                    className: 'marker-cluster-custom',
                    iconSize: L.point(40, 40)
                });
            }
        });
        
        // Sample incident data
        const incidents = [
            { id: 1, lat: 40.7128, lng: -74.0060, type: 'theft', severity: 'medium', description: 'Bicycle theft', date: '2023-05-15', address: '123 Main St' },
            { id: 2, lat: 40.7218, lng: -74.0160, type: 'vandalism', severity: 'low', description: 'Graffiti on building', date: '2023-05-16', address: '456 Park Ave' },
            { id: 3, lat: 40.7028, lng: -73.9960, type: 'assault', severity: 'high', description: 'Physical altercation', date: '2023-05-17', address: '789 Broadway' },
            { id: 4, lat: 40.7328, lng: -74.0260, type: 'accident', severity: 'medium', description: 'Car collision', date: '2023-05-18', address: '321 5th Ave' },
            { id: 5, lat: 40.6928, lng: -73.9860, type: 'theft', severity: 'low', description: 'Pickpocketing', date: '2023-05-19', address: '654 Elm St' },
            { id: 6, lat: 40.7428, lng: -74.0360, type: 'vandalism', severity: 'high', description: 'Property damage', date: '2023-05-20', address: '987 Oak St' },
            { id: 7, lat: 40.6828, lng: -73.9760, type: 'assault', severity: 'medium', description: 'Verbal altercation', date: '2023-05-21', address: '147 Pine St' },
            { id: 8, lat: 40.7528, lng: -74.0460, type: 'accident', severity: 'low', description: 'Minor fender bender', date: '2023-05-22', address: '258 Maple St' },
            { id: 9, lat: 40.6728, lng: -73.9660, type: 'theft', severity: 'high', description: 'Burglary', date: '2023-05-23', address: '369 Cedar St' },
            { id: 10, lat: 40.7628, lng: -74.0560, type: 'vandalism', severity: 'medium', description: 'Broken windows', date: '2023-05-24', address: '741 Birch St' }
        ];
        
        // Function to create a marker for an incident
        function createIncidentMarker(incident) {
            // Determine marker color based on severity
            let markerColor;
            if (incident.severity === 'low') markerColor = '#2ecc71';
            else if (incident.severity === 'medium') markerColor = '#f39c12';
            else markerColor = '#e74c3c';
            
            // Create a custom icon
            const icon = L.divIcon({
                html: `<div style="background-color: ${markerColor}; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"></div>`,
                className: 'incident-marker',
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            });
            
            // Create the marker
            const marker = L.marker([incident.lat, incident.lng], { icon: icon });
            
            // Create popup content
            const popupContent = `
                <div class="incident-popup">
                    <h3>${incident.type.charAt(0).toUpperCase() + incident.type.slice(1)}</h3>
                    <p><strong>Description:</strong> ${incident.description}</p>
                    <p><strong>Date:</strong> ${incident.date}</p>
                    <p><strong>Address:</strong> ${incident.address}</p>
                    <p><strong>Severity:</strong> <span class="severity severity-${incident.severity}">${incident.severity.toUpperCase()}</span></p>
                </div>
            `;
            
            marker.bindPopup(popupContent);
            
            // Add to appropriate cluster based on severity
            if (incident.severity === 'low') {
                lowCluster.addLayer(marker);
            } else if (incident.severity === 'medium') {
                mediumCluster.addLayer(marker);
            } else {
                highCluster.addLayer(marker);
            }
            
            return marker;
        }
        
        // Add all incidents to the map initially
        incidents.forEach(incident => {
            createIncidentMarker(incident);
        });
        
        // Add clusters to the map
        map.addLayer(lowCluster);
        map.addLayer(mediumCluster);
        map.addLayer(highCluster);
        
        // Filter functionality
        document.getElementById('incidentType').addEventListener('change', filterIncidents);
        document.getElementById('severityFilter').addEventListener('change', filterIncidents);
        
        function filterIncidents() {
            const typeFilter = document.getElementById('incidentType').value;
            const severityFilter = document.getElementById('severityFilter').value;
            
            // Remove all clusters from the map
            map.removeLayer(lowCluster);
            map.removeLayer(mediumCluster);
            map.removeLayer(highCluster);
            
            // Clear all clusters
            lowCluster.clearLayers();
            mediumCluster.clearLayers();
            highCluster.clearLayers();
            
            // Filter and add incidents back
            incidents.forEach(incident => {
                const typeMatch = typeFilter === 'all' || incident.type === typeFilter;
                const severityMatch = severityFilter === 'all' || incident.severity === severityFilter;
                
                if (typeMatch && severityMatch) {
                    createIncidentMarker(incident);
                }
            });
            
            // Add clusters back to the map
            map.addLayer(lowCluster);
            map.addLayer(mediumCluster);
            map.addLayer(highCluster);
        }
        
        // Reset view button
        document.getElementById('resetView').addEventListener('click', function() {
            map.setView([40.7128, -74.0060], 12);
            document.getElementById('incidentType').value = 'all';
            document.getElementById('severityFilter').value = 'all';
            filterIncidents();
        });
    </script>
</body>
</html>