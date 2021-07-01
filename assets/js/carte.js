function initCarte() {
    // url du gpx
    const url1 = 'leaflet/geojson/tracks.geojson';
    const url2 = 'leaflet/gpx/activity_2.gpx';
    const c_iconSize = [10, 15];
    const c_shadowSize = [15, 15];
    const c_iconAnchor = [5, 14];
    const c_shadowAnchor = [5, 14];
    const couleur1 = 'blue';
    const couleur2 = 'red';
    
    var carte = L.map('mapid');
    var mainLayer = L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: 'Map data &copy; <a href="http://www.osm.org">OpenStreetMap</a>'
        }).addTo(carte);

    var control = L.control.layers(null, null).addTo(carte);

    console.log('url gpx : '+url1);
    console.log('url gpx : '+url2);

    //Chargement geojson
    // var xmlobj = new XMLHttpRequest();
    // xmlobj.overrideMimeType("application/json");
    // xmlobj.open('GET', url1, true);

    // xmlobj.onreadystatechange = function() {
    //   if (xmlobj.readyState == 4 && xmlobj.status == 200){
    //     callback(xmlobj.responseText)
    //   }
    // };

    // xmlobj.send();

    fetch(url1)
    .then(function (response) {
      return response.json();
    })
    .then(function (data){
      var dataGeojson1 = new L.geoJSON(data, {
        async: true,
        marker_options: {
            startIconUrl: 'leaflet/img/pin-icon-start.png',
            endIconUrl: 'leaflet/img/pin-icon-end.png',
            shadowUrl: 'leaflet/img/pin-shadow.png',
            wptIconUrls: {
                '': 'leaflet/img/pin-icon-wpt.png',
                //Specifier ici le nom du waypoint
              },
              iconSize: c_iconSize,
              shadowSize: c_shadowSize,
              iconAnchor: c_iconAnchor,
              shadowAnchor: c_shadowAnchor,
            },
            polyline_options: {
                color: couleur1
            }
        }).on('loaded', function(e) {
        var dataGeojson1 = e.target;
        carte.fitBounds(dataGeojson1.getBounds());
        }).addTo(carte)});
    
    //Couche gpx
    // var gpx1 = new L.GPX(url1, {
    //     async: true,
    //     marker_options: {
    //         startIconUrl: 'leaflet/img/pin-icon-start.png',
    //         endIconUrl: 'leaflet/img/pin-icon-end.png',
    //         shadowUrl: 'leaflet/img/pin-shadow.png',
    //         wptIconUrls: {
    //             '': 'leaflet/img/pin-icon-wpt.png',
    //             //Specifier ici le nom du waypoint
    //           },
    //           iconSize: c_iconSize,
    //           shadowSize: c_shadowSize,
    //           iconAnchor: c_iconAnchor,
    //           shadowAnchor: c_shadowAnchor,
    //         },
    //         polyline_options: {
    //             color: couleur1
    //         }
    //     }).on('loaded', function(e) {
    //     var gpx = e.target;
    //     carte.fitBounds(gpx.getBounds());
    //     console.log('distance de la trace : '+gpx.get_distance());
    //   }).addTo(carte);

      var gpx2 = new L.GPX(url2, {
        async: true,
        marker_options: {
            startIconUrl: 'leaflet/img/pin-icon-start.png',
            endIconUrl: 'leaflet/img/pin-icon-end.png',
            shadowUrl: 'leaflet/img/pin-shadow.png',
            wptIconUrls: {
                '': 'leaflet/img/pin-icon-wpt.png',
                //Specifier ici le nom du waypoint
              },
              iconSize: c_iconSize,
              shadowSize: c_shadowSize,
              iconAnchor: c_iconAnchor,
              shadowAnchor: c_shadowAnchor,
            },
            polyline_options: {
                color: couleur2
            }
        }).on('loaded', function(e) {
        var gpx = e.target;
        carte.fitBounds(gpx2.getBounds());
        console.log('distance de la trace : '+gpx2.get_distance());


        //Couche waypoints
        // L.marker(gpx2.getDistanceToCoord(url2,10000)).addTo(carte);
      }).addTo(carte);

      
}