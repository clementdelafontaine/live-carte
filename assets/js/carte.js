function initCarte() {
    //url
    const url1 = 'leaflet/geojson/4080/7189.json';
    const url2 = 'leaflet/geojson/4080/7190.json';

    //Paramètres
    const c_iconSize = [30, 40];
    const c_shadowSize = [40, 40];
    const c_iconAnchor = [200, -100];
    const c_shadowAnchor = [15, 43];
    const c_popupAnchor = [-3, -76];

    const c_depart = 'leaflet/img/pin-icon-start.png';
    const c_arrivee = 'leaflet/img/pin-icon-end.png';
    const c_shadow = 'leaflet/img/pin-shadow.png';
    const c_inter = 'leaflet/img/pin-icon-wpt.png';
    const c_ravito = 'leaflet/img/pin-icon-wpt.png';

    //Icones
    var PointIcon = L.Icon.extend({
      options: {
        // shadowUrl: c_shadow,
        // iconSize: c_iconSize,
        // shadowSize: c_shadowSize,
        // shadowAnchor: c_shadowAnchor
      }
    });
    var departIcon = new PointIcon({ iconUrl: c_depart });
    var arriveeIcon = new PointIcon({ iconUrl: c_arrivee });
    var interIcon = new PointIcon({ iconUrl: c_inter });
    var ravitoIcon = new PointIcon({ iconUrl: c_ravito });
    
    //Construction
    var carte = L.map('mapid');
    var mainLayer = L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: 'Map data &copy; <a href="http://www.osm.org">OpenStreetMap</a>'
          ,
          async: true}).addTo(carte);

    var control = L.control.layers(null, null).addTo(carte);

    console.log('url 1 : '+url1);
    console.log('url 2 : '+url2);

    //Chargement geojson
    fetch(url1)
    .then(function (response) {
      return response.json();
    })
    .then(function (data){
      var dataGeojson1 = new L.geoJSON(data, {
        async: true,
        style: function (feature) {
          var type = feature.geometry.type;
          if (type == "MultiLineString") {
            var colorLine = feature.properties.color;
            console.log("couleur : " + colorLine);
            if (colorLine != null) {
              return { color: colorLine };
            }
          }
        },
        pointToLayer: function (feature, latlng) {
          var type = feature.geometry.type;
          console.log("nom de la catégorie du point : " + type);
          if (type == "Point") {
            var cat = feature.properties.category;
            if (cat == "depart")
              iconType = departIcon;
            else if (cat == "arrivee")
              iconType = arriveeIcon;
            else if (cat == "inter")
              iconType = interIcon;
            else if (cat == "ravitaillement")
              iconType = ravitoIcon;
            
            return L.marker(latlng, {
              icon: iconType
            });
          }
        },
        onEachFeature: function (feature, layer) {
          var popupText = "<b>" + feature.properties.popupContent + "</b>";
          if (feature.properties.category == "inter")
            popupText += "<br><a href='" + feature.properties.url + "'>Photos</a>";

          layer.bindPopup(popupText, {
            closeButton: true,
            offset: L.point(0, -20)
          });
        }
      }).addTo(carte);

      carte.fitBounds(dataGeojson1.getBounds());
    });

    //Chargement geojson
    fetch(url2)
    .then(function (response) {
      return response.json();
    })
    .then(function (data){
      var dataGeojson2 = new L.geoJSON(data, {
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
            }
        }).addTo(carte);

        carte.fitBounds(dataGeojson2.getBounds());
      });

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

    // var gpx2 = new L.GPX(url2, {
    //   async: true,
    //   marker_options: {
    //       startIconUrl: 'leaflet/img/pin-icon-start.png',
    //       endIconUrl: 'leaflet/img/pin-icon-end.png',
    //       shadowUrl: 'leaflet/img/pin-shadow.png',
    //       wptIconUrls: {
    //           '': 'leaflet/img/pin-icon-wpt.png',
    //           //Specifier ici le nom du waypoint
    //         },
    //         iconSize: c_iconSize,
    //         shadowSize: c_shadowSize,
    //         iconAnchor: c_iconAnchor,
    //         shadowAnchor: c_shadowAnchor,
    //       },
    //       polyline_options: {
    //           color: couleur2
    //       }
    //   }).on('loaded', function(e) {
    //   var gpx = e.target;
    //   carte.fitBounds(gpx2.getBounds());
    // //   console.log('distance de la trace : '+gpx2.get_distance());
    // //   //Couche waypoints
    // //   // L.marker(gpx2.getDistanceToCoord(url2,10000)).addTo(carte);
    // }).addTo(carte);


      

      
}