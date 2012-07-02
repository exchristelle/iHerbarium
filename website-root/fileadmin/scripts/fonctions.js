// JavaScript Document
/* fonction permettant que la carte soit centr�e sur l\'adresse entr�e par l\'utilisateur */
	function codeAddress() {
		    var address = document.getElementById("address").value;
		    if (geocoder) {
			      geocoder.geocode( { 'address': address}, function(results, status) {
			        if (status == google.maps.GeocoderStatus.OK) {
				        	map.setCenter(results[0].geometry.location);
				          
				            /* quand on trouve une r�ponse � l\'adresse que l\'on a tap�, on fait un zoom sur la position recherch�e
				            L\'utilisateur pourra ainsi faire sa s�lection en cliquant sur le lieu qu\'il souhaite */
				            map.setZoom(13);
				          
				     } 
			         else {
					        alert("La localisation n'a pas pu se faire pour les raisons suivantes : " + status);
			           }
	              });
	       }
	  }

