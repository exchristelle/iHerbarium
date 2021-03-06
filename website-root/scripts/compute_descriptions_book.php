<?php
include("../bibliotheque/common_functions.php");
//truncate `iherbarium_book_specimen`
$project_name = "fdc";

  function clean_text($chaine)
  {
    return str_replace("'",'`',$chaine);
  }
  
  function look_at($id_obs)
  {
    global $project_name;
  bd_connect();
  
  $requete_owner="select  name,url_rewriting_fr,computed_best_tropicos_id
	from iherba_observations,fe_users
	    where iherba_observations.idobs=$id_obs and iherba_observations.id_user = fe_users.uid";
  $lignes_reponse = mysql_query($requete_owner);
  $ligne = mysql_fetch_array($lignes_reponse);
  $taxonid = $ligne['computed_best_tropicos_id'];
  $owner = $ligne['name'];
  $scname = $ligne['url_rewriting_fr'];
  $besttropicos = $ligne['computed_best_tropicos_id'];
  $commonname = "nom commun de ".$ligne['url_rewriting_fr'];
  $texte_descriptif = " en savoir plus... ";
  $langue= 'fr';
  $novice_tag =array();

  $requete_owner="select  * from `iherba_determination` WHERE  `tropicosid` =  $taxonid order by id desc";
  $lignes_reponse = mysql_query($requete_owner);
  if(mysql_num_rows($lignes_reponse)>0)
    {
      $ligne = mysql_fetch_array($lignes_reponse);
      $scname = $ligne['nom_scientifique'];
    }
    
  $requete_owner="select  * from iherba_taxon_texts
	    where taxon = $taxonid AND  `taxon_api` =  'tropicos' AND  `lng` LIKE  'fr' and categorie = 'vernacular' ";
  $lignes_reponse = mysql_query($requete_owner);

  if(mysql_num_rows($lignes_reponse)>0)
    {
      $ligne = mysql_fetch_array($lignes_reponse);
      $commonname = $ligne['description'];
    }
  
  $requete_owner="select  * from iherba_taxon_texts
	    where taxon = $taxonid AND  `taxon_api` =  'tropicos' AND  `lng` LIKE  'fr' and categorie = 'description' ";
  $lignes_reponse = mysql_query($requete_owner);
  if(mysql_num_rows($lignes_reponse)>0)
    {
      $ligne = mysql_fetch_array($lignes_reponse);
      $texte_descriptif = $ligne['description'];
    }
  
  $requete_owner="select  * from iherba_taxon_texts
	    where taxon = $taxonid AND  `taxon_api` =  'tropicos' AND  `lng` LIKE  'fr' and categorie = 'habitat' ";
  $lignes_reponse = mysql_query($requete_owner);
  if(mysql_num_rows($lignes_reponse)>0)
    {
      $ligne = mysql_fetch_array($lignes_reponse);
      $texte_descriptif .= "<br>Habitat :<br>".$ligne['description'];
    }
    
  $requete_owner="select  * from iherba_taxon_texts
	    where taxon = $taxonid AND  `taxon_api` =  'tropicos' AND  `lng` LIKE  'fr' and categorie = 'origine' ";
  $lignes_reponse = mysql_query($requete_owner);
  if(mysql_num_rows($lignes_reponse)>0)
    {
      $ligne = mysql_fetch_array($lignes_reponse);
      $texte_descriptif .= "<br>Origine du nom :<br>".$ligne['description'];
    }
  
  $requete_owner="select  * from iherba_taxon_texts
	    where taxon = $taxonid AND  `taxon_api` =  'tropicos' AND  `lng` LIKE  'fr' and categorie = 'cycle' ";
  $lignes_reponse = mysql_query($requete_owner);
  if(mysql_num_rows($lignes_reponse)>0)
    {
      $ligne = mysql_fetch_array($lignes_reponse);
      $texte_descriptif .= "<br>Cycle :<br>".$ligne['description'];
    }
  $requete_owner="select  * from iherba_taxon_texts
	    where taxon = $taxonid AND  `taxon_api` =  'tropicos' AND  `lng` LIKE  'fr' and categorie = 'medecine' ";
  $lignes_reponse = mysql_query($requete_owner);
  if(mysql_num_rows($lignes_reponse)>0)
    {
      $ligne = mysql_fetch_array($lignes_reponse);
      $texte_descriptif .= "<br>Medecine :<br>".$ligne['description'];
    }
  
  $requete_owner="select  * from iherba_taxon_texts
	    where taxon = $taxonid AND  `taxon_api` =  'tropicos' AND  `lng` LIKE  'fr' and categorie = 'culinaire' ";
  $lignes_reponse = mysql_query($requete_owner);
  if(mysql_num_rows($lignes_reponse)>0)
    {
      $ligne = mysql_fetch_array($lignes_reponse);
      $texte_descriptif .= "<br>Culinaire :<br>".$ligne['description'];
    }
  $requete_owner="select  * from iherba_taxon_texts
	    where taxon = $taxonid AND  `taxon_api` =  'tropicos' AND  `lng` LIKE  'fr' and categorie = 'usage' ";
  $lignes_reponse = mysql_query($requete_owner);
  if(mysql_num_rows($lignes_reponse)>0)
    {
      $ligne = mysql_fetch_array($lignes_reponse);
      $texte_descriptif .= "<br>Usage :<br>".$ligne['description'];
    }
    
  $texte_descriptif = str_replace("'",'`',$texte_descriptif);
  $requete_lignes_pattern="select distinct iherba_roi.id,iherba_roi_answers_pattern.id_roi,
	    iherba_roi_answers_pattern.id_question,
	    iherba_roi_answers_pattern.id_answer_most_common,iherba_roi_answers_pattern.prob_most_common,   iherba_roi_answers_pattern.id_just_less_common, iherba_roi_answers_pattern.prob_just_less,
	    iherba_question.choice_explicitation_one , iherba_question.choice_explicitation_two_seldom , iherba_question.choice_explicitation_two_often , iherba_question.choice_detail ,tag ,texte as legendtag"    /* Kuba -> */  . " , iherba_roi_answers_pattern.id AS lineid " . /* <- Kuba */
	"from iherba_roi_answers_pattern,iherba_roi,iherba_photos,iherba_question,iherba_tags,iherba_roi_tag,iherba_tags_translation
	    where iherba_photos.id_obs=$id_obs and
	    iherba_photos.idphotos=iherba_roi.id_photo and
	    iherba_roi.id=iherba_roi_answers_pattern.id_roi and iherba_question.id_langue='$langue'
	    and
	    iherba_tags.id_tag = iherba_roi_tag.id_tag and iherba_roi_tag.id_roi = iherba_roi.id
	    and
	    iherba_tags_translation.id_tag = iherba_tags.id_tag and iherba_tags_translation.id_langue = '$langue'
	    and
	    iherba_roi_answers_pattern.id_question = iherba_question.id_question  ";
  
  $lignes_reponse = mysql_query($requete_lignes_pattern);
  
  $liste_roi= array();
  $liste_roi_tag= array();
  if(mysql_num_rows($lignes_reponse)>0)
    {
      while ($ligne = mysql_fetch_array($lignes_reponse)) {
		$liste_roi[] = $ligne['id'];
		$liste_roi_tag[] = $ligne['legendtag'];
		$possible_answer = explode('!',$ligne['choice_detail']);
		if( $ligne['id_question']==714)
		      $novice_tag[]= "couleur_".$possible_answer[$ligne['id_answer_most_common']];
		
		if( $ligne['id_question']==707)
		      {
		      if($ligne['id_answer_most_common']==0)
			      $novice_tag[]= "fleur_radiale";
			      else
			      $novice_tag[]= "fleur_radiale";
		      }
		 if( $ligne['id_question']==702)
		      {
		      $novice_tag[]= "petale_ordre".$ligne['id_answer_most_common'];
		      }
		if( $ligne['id_question']==302)
		      {
		      if($ligne['id_answer_most_common']==0)
			  $novice_tag[]= "plante_basse";
		      }
		if( $ligne['id_question']==230)
		      {
		      if($ligne['id_answer_most_common']==0)
			  $novice_tag[]= "feuille_simple";
		      }
			
    }
  }
  
  $requete_lignes_pattern="select distinct iherba_roi.id,iherba_roi_answers_pattern.id_roi,iherba_photos.nom_photo_final,
	  iherba_roi_answers_pattern.id_question,
	  iherba_roi_answers_pattern.id_answer_most_common,iherba_roi_answers_pattern.prob_most_common,   iherba_roi_answers_pattern.id_just_less_common, iherba_roi_answers_pattern.prob_just_less,
	  iherba_question.choice_explicitation_one , iherba_question.choice_explicitation_two_seldom , iherba_question.choice_explicitation_two_often , iherba_question.choice_detail ,tag ,texte as legendtag"    /* Kuba -> */  . " , iherba_roi_answers_pattern.id AS lineid " . /* <- Kuba */
      "from iherba_roi_answers_pattern,iherba_roi,iherba_photos,iherba_question,iherba_tags,iherba_roi_tag,iherba_tags_translation
	  where iherba_photos.id_obs=$id_obs and
	  iherba_photos.idphotos=iherba_roi.id_photo and
	  iherba_roi.id=iherba_roi_answers_pattern.id_roi and iherba_question.id_langue='$langue'
	  and
	  iherba_tags.id_tag = iherba_roi_tag.id_tag and iherba_roi_tag.id_roi = iherba_roi.id
	  and
	  iherba_tags_translation.id_tag = iherba_tags.id_tag and iherba_tags_translation.id_langue = '$langue'
	  and
	  iherba_roi_answers_pattern.id_question = iherba_question.id_question  group by iherba_roi.id";

  $lignes_reponse = mysql_query($requete_lignes_pattern);
  $liste_photo= array();
  $liste_fichier_photo=array();
  $nbphoto=0;
  if(mysql_num_rows($lignes_reponse)>0)
    {
      while ($ligne = mysql_fetch_array($lignes_reponse)) {
		if(!(in_array($ligne['nom_photo_final'] , $liste_fichier_photo)))
		  {
		    $liste_fichier_photo[] = $ligne['nom_photo_final'];
		    $liste_photo[$nbphoto]['tag']= $ligne['tag'];
		    $liste_photo[$nbphoto]['photo'] = "/medias/big/".$ligne['nom_photo_final'];
		    $liste_photo[$nbphoto]['legende'] = "photo de ".$ligne['legendtag'];
		    $liste_photo[$nbphoto]['droits'] = "license iherbarium, photo prise par $owner" ;
		    $nbphoto++;
		  }
	    }
    }
//rajout photo non taggu�es
    $requete_lignes_pattern="select distinct iherba_photos.nom_photo_final from iherba_photos,iherba_question
	  where iherba_photos.id_obs=$id_obs ";
  $lignes_reponse = mysql_query($requete_lignes_pattern);
  
  $nbphoto=0;
  if(mysql_num_rows($lignes_reponse)>0)
    {
      while ($ligne = mysql_fetch_array($lignes_reponse)) {
		if(!(in_array($ligne['nom_photo_final'] , $liste_fichier_photo)))
		  {
		    $liste_fichier_photo[] = $ligne['nom_photo_final'];
		    $liste_photo[$nbphoto]['tag']= "notag";
		    $liste_photo[$nbphoto]['photo'] = "/medias/big/".$ligne['nom_photo_final'];
		    $liste_photo[$nbphoto]['legende'] = "";
		    $liste_photo[$nbphoto]['droits'] = "license iherbarium, photo prise par $owner" ;
		    $nbphoto++;
		  }
	    }
    }

    
  
  $jsonphoto = json_encode($liste_photo);
  $jsontag = json_encode($novice_tag);
  
  $requete_owner="select  * from iherba_taxon_texts
	    where taxon = $taxonid AND  `taxon_api` =  'tropicos' AND  `lng` LIKE  'fr' and categorie = 'tag_systeme' ";
  $lignes_reponse = mysql_query($requete_owner);
  //echo "<br>question tag $requete_owner";
  if(mysql_num_rows($lignes_reponse)>0)
    { $ligne = mysql_fetch_array($lignes_reponse);
      $jsontag = $ligne['description']; //echo "XXXXXXXX $jsontag YYY";
    }
    

  $sql_insert = "INSERT INTO `iherbarium_book_specimen` (`project_name`, `taxonref`, `langue`, `commonname`, `scientificname`, `pictures_with_legends`, `description`, `morphology`)
  VALUES ('$project_name', '$besttropicos', 'fr', '$commonname', '$scname', '$jsonphoto', '$texte_descriptif', '$jsontag');";
  echo $id_obs;//."-".$commonname;echo "<br>.$sql_insert.<br>";
  
 $lignes_reponse = mysql_query($sql_insert); 
}
// recherche liste plante fdc 
 bd_connect();
  $couleur_f = array("blanche","jaune" ,"rose-mauve" ,"rouge" ,"verte","violette-bleue","mauve-rose","bleue-violette","noncodee");
  $port_plante = array("port_dresse","port_nondresse","port_nondresse","port_noncode");
  $genre_feuille = array("feuille_laniere","feuille_allongee","feuille_entiere","feuille_decoupee","feuille_pennee","feuille_trifoliee","feuille_noncodee");
  $vide_table = "delete from iherba_taxon_texts where licence='fdc' ";
  $lignes_reponse = mysql_query($vide_table);
  $vide_table = "delete from iherbarium_book_specimen where project_name='fdc' ";
  $lignes_reponse = mysql_query($vide_table);
  
  $requete_fdc="select  *
	from fiche_fleurs
	    where id_obs >0 and taxon != '1'";
  $lignes_reponse = mysql_query($requete_fdc);
   while ($ligne = mysql_fetch_array($lignes_reponse)) {
    $novice_tag= array();
		$taxon = $ligne['taxon'];
		$nom_commun = clean_text($ligne['nc']);
		$insert_text = "INSERT INTO `typoherbarium`.`iherba_taxon_texts` (`taxon`, `taxon_api`, `lng`, `categorie`, `subproject`, `description`, `licence`, `ts_creation`)
		VALUES ('$taxon', 'tropicos', 'fr', 'vernacular', '', '$nom_commun', 'fdc', CURRENT_TIMESTAMP);";
		$lignes_insert = mysql_query($insert_text);
		
		$habitat = clean_text($ligne['habitatauvergne']);
		if($habitat!="")
		  {
		    $insert_text = "INSERT INTO `typoherbarium`.`iherba_taxon_texts` (`taxon`, `taxon_api`, `lng`, `categorie`, `subproject`, `description`, `licence`, `ts_creation`)
		  VALUES ('$taxon', 'tropicos', 'fr', 'habitat', '', '$habitat', 'fdc', CURRENT_TIMESTAMP);";
		  $lignes_insert = mysql_query($insert_text);
		  }
		  
		$origine = clean_text($ligne['origine']);
		if($origine!="")
		  {
		    $insert_text = "INSERT INTO `typoherbarium`.`iherba_taxon_texts` (`taxon`, `taxon_api`, `lng`, `categorie`, `subproject`, `description`, `licence`, `ts_creation`)
		  VALUES ('$taxon', 'tropicos', 'fr', 'origine', '', '$origine', 'fdc', CURRENT_TIMESTAMP);";
		  $lignes_insert = mysql_query($insert_text);
		  }
		  
		$monusage = clean_text($ligne['monusage']);
		if($monusage!="")
		  {
		    $insert_text = "INSERT INTO `typoherbarium`.`iherba_taxon_texts` (`taxon`, `taxon_api`, `lng`, `categorie`, `subproject`, `description`, `licence`, `ts_creation`)
		  VALUES ('$taxon', 'tropicos', 'fr', 'usage', '', '$monusage', 'fdc', CURRENT_TIMESTAMP);";
		  $lignes_insert = mysql_query($insert_text);
		  }
		  
		$description = clean_text($ligne['description']);
		echo " taxon $taxon";
		if($description!="")
		  { 
		    $insert_text = "INSERT INTO `typoherbarium`.`iherba_taxon_texts` (`taxon`, `taxon_api`, `lng`, `categorie`, `subproject`, `description`, `licence`, `ts_creation`)
		  VALUES ('$taxon', 'tropicos', 'fr', 'description', '', '$description', 'fdc', CURRENT_TIMESTAMP);";
		  $lignes_insert = mysql_query($insert_text);
		  
		  }
		echo "<br>";
		$cycle = clean_text($ligne['cycle']);
		if($cycle!="")
		  {
		    $insert_text = "INSERT INTO `typoherbarium`.`iherba_taxon_texts` (`taxon`, `taxon_api`, `lng`, `categorie`, `subproject`, `description`, `licence`, `ts_creation`)
		  VALUES ('$taxon', 'tropicos', 'fr', 'cycle', '', '$cycle', 'fdc', CURRENT_TIMESTAMP);";
		  $lignes_insert = mysql_query($insert_text);
		  }
		
		$culinaire = clean_text($ligne['culinaire']);
		if($culinaire!="")
		  {
		    $insert_text = "INSERT INTO `typoherbarium`.`iherba_taxon_texts` (`taxon`, `taxon_api`, `lng`, `categorie`, `subproject`, `description`, `licence`, `ts_creation`)
		  VALUES ('$taxon', 'tropicos', 'fr', 'culinaire', '', '$culinaire', 'fdc', CURRENT_TIMESTAMP);";
		  $lignes_insert = mysql_query($insert_text);
		  }
		
		$medecine = clean_text($ligne['medecine']);
		if($medecine!="")
		  {
		    $insert_text = "INSERT INTO `typoherbarium`.`iherba_taxon_texts` (`taxon`, `taxon_api`, `lng`, `categorie`, `subproject`, `description`, `licence`, `ts_creation`)
		  VALUES ('$taxon', 'tropicos', 'fr', 'medecine', '', '$medecine', 'fdc', CURRENT_TIMESTAMP);";
		  $lignes_insert = mysql_query($insert_text);
		  }
		  
		$novice_tag[]= $couleur_f[$ligne['kod_coul_fleurs']];
		$novice_tag[]= $port_plante[$ligne['kod_port']] ;
		$novice_tag[]= $genre_feuille[$ligne['kod_feuille']] ;
		echo "<br>".$nom_commun . " -  ".$ligne['taillelmax']. "  - ".$ligne['taillemin']." - fin -";
		if($ligne['taillelmax']<25)
			{$novice_tag[]="plante_petite"; }
		if($ligne['taillemin']>70)
			$novice_tag[]="plante_grande";	
		
		$jsontag = json_encode($novice_tag);
		$insert_text = "INSERT INTO `typoherbarium`.`iherba_taxon_texts` (`taxon`, `taxon_api`, `lng`, `categorie`, `subproject`, `description`, `licence`, `ts_creation`)
		  VALUES ('$taxon', 'tropicos', 'fr', 'tag_systeme', '', '$jsontag', 'fdc', CURRENT_TIMESTAMP);";
		  $lignes_insert = mysql_query($insert_text);
		
		look_at($ligne['id_obs']);
		//$cpt++;if($cpt>12)die();
	    }
	    
/* pere lachaise
look_at(210);
look_at(356);
look_at(810);
look_at(378);
look_at(293);
look_at(374);
look_at(330);
look_at(158);
look_at(359);
look_at(107);
look_at(466);
look_at(813);
look_at(1467);
look_at(866);
look_at(589);
look_at(443);
look_at(870);
look_at(242);
look_at(357);
look_at(221);
look_at(1029);
look_at(1558);
look_at(444);
look_at(950);
look_at(294);
look_at(291);
look_at(168);
look_at(207);
look_at(441);

*/
?>
