<?php
// export to a xml file a set of observations
$csv_field_separator = ";";
$csv_field_border = '"';

include("../bibliotheque/common_functions.php");
bd_connect();

$myzone = 3;
if(isset($_GET['numerozone']))if(is_numeric($_GET['numerozone']))$myzone=desamorcer($_GET['numerozone']);

$targetzone = 33;
if(isset($_GET['targetzone']))if(is_numeric($_GET['targetzone']))$targetzone=desamorcer($_GET['targetzone']);


$list_zones = "select * from iherba_indicateurs_zones where uid=$myzone";
$result_ref = mysql_query($list_zones)or die ('Erreur SQL !'.$list_zones.'<br />'.mysql_error());
$row_zone= mysql_fetch_assoc($result_ref) ;

$fieldname_lat = "latitude";
$fieldname_long = "longitude";

$nb_square_lat = $row_zone['nb_square_lat'];
$nb_square_long = $row_zone['nb_square_long'];
$startlat = $row_zone['startlat'];
$startlong=  $row_zone['startlong'];
    
$deltalat = $row_zone['deltalat'];
$deltalong = $row_zone['deltalong'];

// modify this when list of quadrat is used
$wheresql = " AND $fieldname_lat > ".$startlat. " AND $fieldname_lat <= ".($startlat+(($nb_square_lat + 1)* $deltalat)). " ";
$wheresql .=  " AND $fieldname_long > ".$startlong. " AND $fieldname_long <= ".($startlong+(($nb_square_long + 1)* $deltalong));
    

$base_request = "
SELECT * 
FROM  `iherba_observations` , iherba_determination, fe_users
WHERE iherba_observations.id_user = fe_users.uid
AND id_obs = idobs
AND iherba_determination.tropicosid !=''
" . $wheresql;

bd_connect();
$result_sure = mysql_query($base_request)or die ('Erreur SQL !'.$base_request.'<br />'.mysql_error());
//echo $base_request;
$n=0;
$xmlfile = '<?xml version="1.0" encoding="utf-8"?><pma_xml_export version="1.0">
        <database name="typoherbarium">
        <!-- Table indicabio_inventaire -->
        ';
$csv_file ="";

while($row_obs= mysql_fetch_assoc($result_sure) ){
        $n++;
                
        $xmlfile .= '<table name="indicabio_inventaire">';
        $xmlfile .= ' <column name="set_id">'.$targetzone.'</column>
            <column name="origin_uid">'.$row_obs['idobs'].'</column>
            <column name="taxon">'.$row_obs['tropicosid'].'</column>
            <column name="observation_ts">'.$row_obs['deposit_timestamp'].'</column>
            <column name="latitude">'.$row_obs['latitude'].'</column>
            <column name="longitude">'.$row_obs['longitude'].'</column>
            <column name="quality">0.97</column>
            <column name="user_ref">'.$row_obs['id_user'].'</column>
           <column name="computed_species_name"><![CDATA['.$row_obs['nom_scientifique'].']]></column>
            <column name="computed_genus_name">'.$row_obs['genre'].'</column>
            <column name="computed_family_name">'.$row_obs['famille'].'</column>
        </table>';
        $csv_file .= $csv_field_border.$targetzone.$csv_field_border.$csv_field_separator;
        $csv_file .= $csv_field_border.$row_obs['idobs'].$csv_field_border.$csv_field_separator;
        $csv_file .= $csv_field_border.$row_obs['tropicosid'].$csv_field_border.$csv_field_separator;
        $csv_file .= $csv_field_border.$row_obs['deposit_timestamp'].$csv_field_border.$csv_field_separator;
        $csv_file .= $csv_field_border.$row_obs['latitude'].$csv_field_border.$csv_field_separator;
        $csv_file .= $csv_field_border.$row_obs['longitude'].$csv_field_border.$csv_field_separator;
        $csv_file .= $csv_field_border.'0.97.'.$csv_field_border.$csv_field_separator;
        $csv_file .= $csv_field_border.$row_obs['id_user'].$csv_field_border.$csv_field_separator;
        $csv_file .= $csv_field_border.$row_obs['nom_scientifique'].$csv_field_border.$csv_field_separator;
        $csv_file .= $csv_field_border.$row_obs['genre'].$csv_field_border.$csv_field_separator;
        $csv_file .= $csv_field_border.$row_obs['famille'].$csv_field_border;
        $csv_file .= "\n";
        }

echo "<br>Zone : $myzone ; Nb d'observations : $n ";

$xmlfile .= '    </database> </pma_xml_export>';
$filename = "export_inventaire_".rand(100,900)."_".$myzone.".xml";
$filename_csv = "export_inventaire_".rand(100,900)."_".$myzone.".csv";
file_put_contents($filename,$xmlfile);
file_put_contents($filename_csv,$csv_file);

//you can download the xml file from the web browser
echo "<a href=http://www.iherbarium.fr/scripts/".$filename." >Lien vers le fichier xml</a>"."<br>";
echo "<a href=http://www.iherbarium.fr/scripts/".$filename_csv." >Lien vers le fichier csv</a>"."<br>";

// we can't manage xml file now because of limitation of mysql server 5.1
// $data = base64_encode($xmlfile);
$data = base64_encode($csv_file);

$params = array(
      'http' => array(
                'method' => 'POST',
                'header'=>
                "Accept-language: en\r\n".
                "Content-type: application/x-www-form-urlencoded\r\n",
                'content'=>http_build_query(array('setid'=>$targetzone ,'data'=>$data ))
                )
        );
$ctx = stream_context_create($params);
$resultat = file_get_contents('http://calcul.indicateurs-biodiversite.com/management/receive_data.php',false,$ctx);

?>
