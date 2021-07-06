function initCarte() {
  //Récupération de l'idEpreuve
  var div = document.getElementById("idEpreuve");
  const idEpreuve = div.textContent;
  console.log("idEpreuve : " + idEpreuve);

  //Paramètres
  const c_iconSize = [25, 40];
  const c_shadowSize = [50, 50];
  const c_iconAnchor = [12, 35];
  const c_shadowAnchor = [15, 40];
  const c_popupAnchor = [-3, -76];

  const c_depart = 'leaflet/img/pin-icon-wpt.png';//'leaflet/img/pin-icon-start.png';
  const c_arrivee = 'leaflet/img/pin-icon-wpt.png';//'leaflet/img/pin-icon-end.png';
  const c_shadow = 'leaflet/img/pin-shadow.png';
  const c_inter = 'leaflet/img/pin-icon-wpt.png';
  const c_ravito = 'leaflet/img/pin-icon-wpt.png';

  //Icones
  var PointIcon = L.Icon.extend({
    options: {
      shadowUrl: c_shadow,
      iconSize: c_iconSize,
      iconAnchor: c_iconAnchor,
      shadowSize: c_shadowSize,
      shadowAnchor: c_shadowAnchor
    }
  });
  var departIcon = new PointIcon({ iconUrl: c_depart });
  var arriveeIcon = new PointIcon({ iconUrl: c_arrivee });
  var interIcon = new PointIcon({ iconUrl: c_inter });
  var ravitoIcon = new PointIcon({ iconUrl: c_ravito });

  

  //Construction
  var carte = L.map('mapid');
  var mainLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}', {
    attribution: 'Tiles &copy; Esri &mdash; Esri, DeLorme, NAVTEQ, TomTom, Intermap, iPC, USGS, FAO, NPS, NRCAN, GeoBase, Kadaster NL, Ordnance Survey, Esri Japan, METI, Esri China (Hong Kong), and the GIS User Community'
    // = L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    //   attribution: 'Map data &copy; <a href="http://www.osm.org">OpenStreetMap</a>'
    ,
    async: true
  }).addTo(carte);
  carte.setView([51.505, -0.09], 13);

  // Boutons de l'interface
  var control = L.control.layers(null, null).addTo(carte);

  //Chargement geojson
  //chargement des urls
  readTextFile("leaflet/geojson/7232/url.json", function (text) {
    var url = JSON.parse(text);
    console.log(url);
  }).then(function(url) {
    for (let i = 0; i < url.length; i++) {
      fetch(url[i].url)
        .then(function (response) {
          return response.json();
        })
        .then(function (data) {
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
    }
  });
}

function readTextFile(file, callback) {
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.overrideMimeType("application/json");
  xmlhttp.open("GET", file, true);
  xmlhttp.onreadystatechange = function () {
    console.log("readyState : " + this.readyState + " status : " + this.status);
    if (this.readyState == 4 && this.status == 200) {
      callback(this.responseText);
    }
  }
  xmlhttp.send(null);
}
