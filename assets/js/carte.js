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

  

  // Construction de la partie carte et chargement des tuiles
  var plan = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}', {
    attribution: 'Tiles &copy; Esri &mdash; Esri, DeLorme, NAVTEQ, TomTom, Intermap, iPC, USGS, FAO, NPS, NRCAN, GeoBase, Kadaster NL, Ordnance Survey, Esri Japan, METI, Esri China (Hong Kong), and the GIS User Community'}),
    Esri_WorldImagery = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
    attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
  });

  var carte = L.map('mapid', {
    center: [43.611395, 3.868939],
    zoom: 10,
    layers: [Esri_WorldImagery, plan]
  });

  var baseMaps = {
    "Satellite": Esri_WorldImagery,
    "Plan": plan
  };

  // Boutons de l'interface
  //Echelle
  var scale = L.control.scale();
  scale.addTo(carte);

  let dataGeojson = new Array();
  let noms = new Array();
  var parcoursCourants = new L.FeatureGroup();

  //Chargement geojson
  //#TODO créer une promise contenant la boucle for
  //chargement des urls
  readTextFile("leaflet/geojson/"+idEpreuve+"/url.json")
  .then(function(text) {
    var url = JSON.parse(text);
    console.log(url);
    var nbCouches = url.length;
    for (let i = 0; i < nbCouches; i++) {
      fetch(url[i].url)
        .then(function (response) {
          return response.json();
        })
        .then(function (data) {
          // Création de la couche
          dataGeojson[i] = new L.geoJSON(data, {
            style: function (feature) {
              var type = feature.geometry.type;
              if (type == "MultiLineString") {
                var colorLine = feature.properties.color;
                noms[i] = feature.properties.name + " - " + feature.properties.distance + " km";
                console.log("couleur : " + colorLine);
                if (colorLine != null) {
                  return { color: colorLine };
                }
              }
            },
            pointToLayer: function (feature, latlng) {
              var type = feature.geometry.type;
              console.log("nom de la catégorie de l'objet : " + type);
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
              var popupText = "<b>" + feature.properties.name + "</b>";
              if (feature.properties.category == "inter")
                popupText += "<br><a href='" + feature.properties.url + "'>Photos</a>";
              popupText += "<p>"+noms[i]+"</p>";
              if (typeof feature.properties.popupContent !== 'undefined')
                popupText += "<p>"+feature.properties.popupContent+"</p>";

              layer.bindPopup(popupText, {
                closeButton: true,
                offset: L.point(0, -20)
              });
            }
          }).addTo(carte);
          //Fin création de la couche i
        }).then(function (parcoursCourant) {
          //La couche parcoursCourants permet d'adapter le zoom à tous les parcours au chargement de la carte mais n'est pas affiché sur la carte
          parcoursCourants.addLayer(dataGeojson[i]);
          carte.fitBounds(parcoursCourants.getBounds());
        });
        //Fin for
    }
    setTimeout (function() {
      var parcours = {
      };
      for (let i = 0; i < nbCouches; i++) {
        parcours[noms[i]] = dataGeojson[i];
      }
      L.control.layers(baseMaps,parcours).addTo(carte);
  }, 1000);
  });
}

//Récupération d'un fichier texte dans une promise
function readTextFile(file, callback) {
  return new Promise((resolve, reject) => {
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.overrideMimeType("application/json");
  xmlhttp.open("GET", file, true);
  xmlhttp.onreadystatechange = function () {
    console.log("readyState : " + this.readyState + " status : " + this.status);
    if (this.readyState == 4 && this.status == 200) {
      resolve(this.responseText);
    }
  }
  xmlhttp.send(null);
})
}
