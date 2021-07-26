var plan, carte, baseMaps, scale;
async function initCarte() {
  //Récupération de l'idEpreuve
  var div = document.getElementById('idEpreuve');
  const idEpreuve = div.textContent;
  console.log('idEpreuve : ' + idEpreuve);

  //Vérification si dans le dossier temp
  var div = document.getElementById('temp');
  console.log('temp div content : ' + div.textContent);
  var temp = (div.textContent == 'temp');
  if (temp)
    var root = '/temp/';
  else
    var root = '/';

  //Paramètres
  const c_iconSize = [25, 40];
  const c_shadowSize = [50, 50];
  const c_iconAnchor = [12, 35];
  const c_shadowAnchor = [15, 40];
  const c_popupAnchor = [-3, -76];

  const c_depart = root+'leaflet/img/pin-icon-wpt.png';//'leaflet/img/pin-icon-start.png';
  const c_arrivee = root+'leaflet/img/pin-icon-wpt.png';//'leaflet/img/pin-icon-end.png';
  const c_shadow = root+'leaflet/img/pin-shadow.png';
  const c_inter = root+'leaflet/img/pin-icon-wpt.png';
  const c_ravito = root+'leaflet/img/pin-icon-wpt.png';

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
  carteDeBase();

  let dataGeojson = new Array();
  let noms = new Array();
  var parcoursCourants = new L.FeatureGroup();

  //Chargement geojson
  //#TODO créer une promise contenant la boucle for
  //chargement des urls
  readTextFile(root+'leaflet/geojson/' + idEpreuve + '/url.json')
    .then(function (text) {
      var url = JSON.parse(text);
      console.log(url);
      var nbCouches = url.length;
      for (let i = 0; i < nbCouches; i++) {
        fetch(root+url[i].url)
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
                popupText += "<p>" + noms[i] + "</p>";
                if (typeof feature.properties.popupContent !== 'undefined')
                  popupText += "<p>" + feature.properties.popupContent + "</p>";

                layer.bindPopup(popupText, {
                  closeButton: true,
                  offset: L.point(0, -20)
                });
              }
            });
            //Fin création de la couche i
            //La couche parcoursCourants permet d'adapter le zoom à tous les parcours au chargement de la carte mais n'est pas affiché sur la carte
            parcoursCourants.addLayer(dataGeojson[i]);
          })
      } // Fin for
      setTimeout(function () {
        // Zoom sur les zones avec animation
        carte.flyToBounds(parcoursCourants.getBounds(), {
          duration: 4,
          easeLinearity: 2
        });
      }, 1500);
      return {
        "nbCouches": nbCouches,
        "dataGeojson": dataGeojson
      };
    })
    .then(function(data) {
      setTimeout(function () {
      // Ajout des couches dans le panneau latéral et affichage des parcours sur la carte après l'animation
        var parcours = {
        };
        for (let i = 0; i < data.nbCouches; i++) {
          parcours[noms[i]] = data.dataGeojson[i];
          data.dataGeojson[i].addTo(carte);
        }
        L.control.layers(baseMaps, parcours).addTo(carte);
      }, 5000);
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

function carteDeBase() {
  // Construction de la partie carte et chargement des tuiles
  plan = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}', {
    attribution: 'Tiles &copy; Esri &mdash; Esri, DeLorme, NAVTEQ, TomTom, Intermap, iPC, USGS, FAO, NPS, NRCAN, GeoBase, Kadaster NL, Ordnance Survey, Esri Japan, METI, Esri China (Hong Kong), and the GIS User Community'
  }),
    Esri_WorldImagery = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
      attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
    });

  carte = L.map('mapid', {
    center: [46.676105
      , 2.550920],
    zoom: 4,
    layers: [Esri_WorldImagery, plan]
  });

  baseMaps = {
    "Satellite": Esri_WorldImagery,
    "Plan": plan
  };

  // Boutons de l'interface
  //Echelle
  scale = L.control.scale();
  scale.addTo(carte);

  if  (document.getElementById('idEpreuve').textContent == '') {
    L.control.layers(baseMaps, null).addTo(carte);
  }
}