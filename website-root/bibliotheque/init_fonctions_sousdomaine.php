<?php 
global $etat_sousdomaine ;
global $etat_description_sousdomaine ;
global $etat_requete_where ;

function exists_sousdomaine($sousdomaine,&$data)
{
 // protect from injection sql (from character authorized in url
  $sousdomaine = str_replace("'","?",$sousdomaine);

  //detect if a species is called
 $posuser = strpos($sousdomaine,"species_");
  if($posuser===0)
    {
      $refuser = substr($sousdomaine,8);
          $data[ref] = $refuser;
          $data[type_ssdomaine]='species';
          return true;
    }
  $posuser = strpos($sousdomaine,"user_");
  if($posuser===0)
    {
      $refuser = substr($sousdomaine,5);
      bd_connect();
      $sql="select * from fe_users where uid='$refuser'";
      $result = mysql_query($sql) or die ();
      $nb_lignes_resultats=mysql_num_rows($result);
      $data  = array();
      if($nb_lignes_resultats>0){
	  $data = mysql_fetch_assoc($result);
	  $data[type_ssdomaine]='user';
	  return true;
      }
      
    }
    else
    $refuser = 0;
  bd_connect();
  $sql="select * from iherba_area where name='$sousdomaine'";
  $result = mysql_query($sql) or die ();
  $nb_lignes_resultats=mysql_num_rows($result);
  $data  = array();
  if($nb_lignes_resultats>0){
	  $data = mysql_fetch_assoc($result);
	  $data[type_ssdomaine]='area';
	  return true;
   }
   else {
    return false;
   }
}

function set_sousdomaine($sousdomaine,$area)
  {
  global $etat_sousdomaine ;
  global $etat_description_sousdomaine ;
  global $etat_requete_where ;

  $etat_sousdomaine = $sousdomaine;
  
  //default value
  $etat_requete_where = " 1 ";
  $etat_description_sousdomaine = "";
  //limition to an area  
  if(($etat_sousdomaine != "www")&&($area[type_ssdomaine]=='area')&&($etat_sousdomaine != "wwwtest")){
    $etat_requete_where = " iherba_observations.latitude >".($area['center_lat']-$area['radius']). "";
    $etat_requete_where .= " AND iherba_observations.latitude < ".($area['center_lat']+$area['radius']). "";
    $etat_requete_where .= " AND iherba_observations.longitude > ".($area['center_long']-$area['radius']). "";
    $etat_requete_where .= " AND iherba_observations.longitude < ".($area['center_long']+$area['radius']). "";
    
    $etat_description_sousdomaine = $area['areaname'];
  }
 //limitation to a user 
  if(($etat_sousdomaine != "www")&&($area[type_ssdomaine]=='user')&&($etat_sousdomaine != "wwwtest")){
    $etat_requete_where =  " `iherba_observations`.id_user = '".$area['uid']."' " ;
    
    $etat_description_sousdomaine = "User : ".$area['name'];
  }

 //limitation to a species
  if(($etat_sousdomaine != "www")&&($area[type_ssdomaine]=='species')&&($etat_sousdomaine != "wwwtest")){
      $etat_requete_where =  " `iherba_observations`.computed_best_tropicos_id = '".$area['ref']."' " ;
      $etat_description_sousdomaine = "Species : ".$area;
  }
  
}

function get_description_sousdomaine($language = "fr")
{
  global $etat_description_sousdomaine ;

  // prevoir gestion langue
  return $etat_description_sousdomaine;
}

function get_requete_where_sousdomaine($language = "fr")
{
  global $etat_requete_where ;
  // prevoir gestion langue
  return $etat_requete_where;
}

function get_sousdomaine()
{
  global $etat_sousdomaine ;
  return $etat_sousdomaine;
}

function is_sousdomaine_www()
{
  global $etat_sousdomaine ;
  if(($etat_sousdomaine == "www")||($etat_sousdomaine == "api-test")||($etat_sousdomaine == "wwwtest"))
    return true;
      else
    return false;
}
?>
