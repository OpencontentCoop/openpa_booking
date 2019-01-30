{if and( $attribute.content.latitude, $attribute.content.longitude )}

  {ezscript_require( array( 'ezjsc::jquery', 'leaflet/leaflet.0.7.2.js', 'leaflet/Leaflet.MakiMarkers.js', 'leaflet/leaflet.markercluster.js') )}
  {ezcss_require( array( 'leaflet/leaflet.css', 'leaflet/map.css', 'leaflet/MarkerCluster.css', 'leaflet/MarkerCluster.Default.css' ) )}

  <div id="map-{$attribute.id}" style="width: 100%; height: 200px;"></div>
  <p class="goto">
    <a class="btn btn-xs btn-success pull-right" target="_blank" href="https://www.google.com/maps/dir//'{$attribute.content.latitude},{$attribute.content.longitude}'/@{$attribute.content.latitude},{$attribute.content.longitude},15z?hl=it">Come arrivare <i class="fa fa-external-link"></i></a>
  </p>

  {run-once}
  {literal}
    <script type="text/javascript">
      var drawMap = function(latlng,id){
        var map = new L.Map('map-'+id);
        map.scrollWheelZoom.disable();
        var customIcon = L.MakiMarkers.icon({icon: "star", color: "#f00", size: "l"});
        var postMarker = new L.marker(latlng,{icon:customIcon});
        postMarker.addTo(map);
        map.setView(latlng, 18);
        L.tileLayer('//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
      }
    </script>
  {/literal}
  {/run-once}

  <script type="text/javascript">
    drawMap([{$attribute.content.latitude},{$attribute.content.longitude}],{$attribute.id});
  </script>

{/if}
