<?php
include_once('simple_html_dom.php');
include_once('Controll.php');

//exec('ls -al > C:\Programme\Vidalia Bundle\Tor\tor.exe & ');
//echo $output;
$Herrsteller_liste_contetnt=get2DArrayFromCsv("Hersteller.txt",";");
foreach($Herrsteller_liste_contetnt as $herrsteller){
    
     $vergleich_liste=array();
    /////////////////////////////////////////////////////////////////////////////////
    $Herrsteller_Ordner=$herrsteller[0].'/';
    $Artikle_liste=$Herrsteller_Ordner.$herrsteller[1];
    $Input_korriegern_liste=$Herrsteller_Ordner.$herrsteller[2];
	
    $Output_korriegern_liste=$Herrsteller_Ordner.'Korrektor_'.$herrsteller[2];
    $Herrsteller_Name=$herrsteller[0];
    $output_preise_liste=$Herrsteller_Ordner.'Preis_liste_'.$herrsteller[0].'.csv';
	$Filter_liste=$Herrsteller_Ordner."Filter_Liste_".$Herrsteller_Name.".csv";
    
    //////////////////////DATEINE VORBEREITEN  /////////////////////////////////////////////
    $Artikle_liste_contetnt=get2DArrayFromCsv($Artikle_liste,";");
    $korriegern_liste_contetnt=get2DArrayFromCsv($Input_korriegern_liste,";");
    $Filter_output=fopen($Filter_liste, 'w+');
    $Filter_columns=array('Artikle_Num','preis');
    fputcsv($Filter_output,$Filter_columns,";"," ");
    $output_preis_liste_handel = fopen($output_preise_liste, 'w+');
    $columns=array('Artikle_Num','Autokrake_preis','Daparto_min_preis','Mitbewerber_Name','Neu_AK_preis_ohne_Versand','Billigster?');
    fputcsv($output_preis_liste_handel, $columns,";"," ");
    echo "<br>Before Call Memory Usage: ".memory_get_usage()."\n";
    $i=0;
    ////////////////////// LOOP ANFANGEN MIT DEN ARTIKELEN///////////////////////////////////////////////
    $besoders_vergleich_liste=array();
    foreach($Artikle_liste_contetnt as $Artikle){
            $i++;
       
            if($i==19){/// NEUE IP
			
                    if (tor_new_identity('127.0.0.1', '9051')) {
                            echo "Identity switched!";sleep(4);
                    }
					else{echo 'Fail';}
                    $i=0;
            }
            $artikel_num=str_replace(" ","",$Artikle[0]);
            
            $url='http://www.daparto.de/Teilenummernsuche/'.$Herrsteller_Name.'/'.$artikel_num;
			echo $url."\n";
            $store=curl_get_html($url); 
          
            $html = new simple_html_dom();
            $html->load($store);
            $ret_auto_liefrate = $html->find('table tr[itemprop=offers]');
            if(!$ret_auto_liefrate){
               if($store=="Curl error: couldn't connect to host"){
                tor_new_identity('127.0.0.01', '9050');
            }
            sleep(1);
            $store=curl_get_html($url);
            $html->load($store);
            $ret_auto_liefrate = $html->find('table tr[itemprop=offers]');
            }
            if($ret_auto_liefrate){
                    // print_r($ret_auto_liefrate);
                    $DP_name_array=array();
                    $DP_versand=array();
                    foreach ($ret_auto_liefrate as  $offer){
                                    $offer_price=$offer->find('meta[itemprop=price]');
                                    $offer_name=$offer->find('meta[itemprop=name]'); 
                                    $offer_versand_obj=$offer->find('ul[class=orderInfo]');
                                    $offer_versand_obj=$offer_versand_obj[0];
                                    $offer_versand_obj= $offer_versand_obj->find('li', 1);
                                    $offer_versand=get_punkt_num($offer_versand_obj->innertext,1);
                                    $name=trim($offer_name[0]->content);
                                    $preis_punkt=str_replace(",",".",str_replace(".","",$offer_price[0]->content));
                                    $summe=$preis_punkt+$offer_versand;
                                    if($name=='autokrake.de'){$aktuell_AK_preis=$summe;}
                                    elseif($name!='autokrake.de'){
                                            $DP_summe_preis_array[$name]=$summe;
                                            $DP_name_array["$summe"]=$name;
                                            $DP_versand["$summe"]=$offer_versand;

                                            }

                            }

            }
            else{
            $Filter_columns=array($Artikle[0],$Artikle[1]);
            fputcsv($Filter_output,$Filter_columns,";"," ");
            $Billigster='KEINE ERGEBNISSE';
            $min_preis_mitbewerber_versand='Null';
            }

            /////////////////////////////////////////////////////////////////////////////////////////////////////
            if($DP_summe_preis_array){$min_preis_mitbewerber_versand=min($DP_summe_preis_array);}else{$min_preis_mitbewerber_versand='Null';}
            if($Artikle[1]!=''){
                $EK_AK_preis=comma_2_punkt(str_replace("'","",$Artikle[1]));
                $VK_AK_preis_ohne_versand=percent($EK_AK_preis);
                $VK_AK_preis_versand= $VK_AK_preis_ohne_versand+5.95;
                }
                else{$EK_AK_preis='Null';}
                if($min_preis_mitbewerber_versand!='Null'){
                                  
                                   if($VK_AK_preis_versand > $min_preis_mitbewerber_versand ){$neu_AK_preis_ohne_versand=$VK_AK_preis_ohne_versand;$Billigster='Nein';}
                                   elseif($VK_AK_preis_versand < $min_preis_mitbewerber_versand){$neu_AK_preis_ohne_versand=$min_preis_mitbewerber_versand-6.00;$Billigster='JA';}         
                }
            elseif($min_preis_mitbewerber_versand=='Null'&& $aktuell_AK_preis){$neu_AK_preis_ohne_versand='Null';$Billigster='AK ist die einzige anbieter';}
            elseif($min_preis_mitbewerber_versand=='Null'&& !$aktuell_AK_preis){$neu_AK_preis_ohne_versand='Null';$Billigster='Keine';}
            else{$Billigster='Keine';}
            if($aktuell_AK_preis){
                $aktuell_AK_preis_ohne_versand=$aktuell_AK_preis-5.95;
            }
            else{$aktuell_AK_preis='Keine';}
            if($min_preis_mitbewerber_versand!='Null'){
                $Mitbewerber_name_minPreis=$DP_name_array["$min_preis_mitbewerber_versand"];
                $min_preis_mitbewerber_versand_ohne_versand=punkt_2_comma($min_preis_mitbewerber_versand-$DP_versand["$min_preis_mitbewerber_versand"]);
            }
            else{
                $Mitbewerber_name_minPreis='Keine';
                $min_preis_mitbewerber_versand='Null';
            }
            //////////////////////////////
			
           
            $vergleich=array();
            $vergleich[0]=$Artikle[0];
            $vergleich[1]=punkt_2_comma($VK_AK_preis_versand);
            $vergleich[2]=punkt_2_comma($min_preis_mitbewerber_versand_ohne_versand);
            $vergleich[3]=$DP_name_array["$min_preis_mitbewerber_versand"];
            $vergleich[4]=punkt_2_comma($neu_AK_preis_ohne_versand);
            $vergleich[5]=$Billigster;
            $vergleich_liste[$Artikle[0]]=$vergleich;
            //elseif(!$aktuell_AK_preis){$neu_AK_preis_ohne_versand=$min_preis_mitbewerber_versand-6.00;}
           // echo '<br>neues preis ist'.$neu_AK_preis_ohne_versand.'<br>';
            $besoders_vergleich=array();
			$besoders_vergleich[0]=$Artikle[0];
			$besoders_vergleich[1]=str_replace(".",",",$aktuell_AK_preis_ohne_versand);
			$besoders_vergleich[2]=$DP_name_array["$min_preis_mitbewerber_versand"];
			$besoders_vergleich[3]=str_replace(".",",",$min_preis_mitbewerber_versand-$DP_versand["$min_preis_mitbewerber_versand"]);                       
            $besoders_vergleich_liste[$Artikle[0]]=$besoders_vergleich;
			$preis_liste_array=array();
            $preis_liste_array[0]=$Artikle[0];
            $preis_liste_array[1]=punkt_2_comma($aktuell_AK_preis_ohne_versand);
            $preis_liste_array[2]=$min_preis_mitbewerber_versand_ohne_versand;
            $preis_liste_array[3]=$Mitbewerber_name_minPreis;
            $preis_liste_array[4]=punkt_2_comma($neu_AK_preis_ohne_versand);
            $preis_liste_array[5]=$Billigster;
            //$columns=array($Artikle[0],punkt_2_comma($aktuell_AK_preis),$min_preis_mitbewerber_versand,$Mitbewerber_name_minPreis,punkt_2_comma($neu_AK_preis_ohne_versand),$Billigster);
            fputcsv($output_preis_liste_handel, $preis_liste_array,";",'"');

            if($min_preis_mitbewerber_versand!='Null' ){
                foreach($korriegern_liste_contetnt as $key =>$portal_replace){
				
                $artikle_num_korrektor=get_aritikel_Nummer($portal_replace['1']);
                if(str_replace(" ","",$Artikle[0])==$artikle_num_korrektor){
                   
                    $korriegern_liste_contetnt[$key]['4']=punkt_2_comma($neu_AK_preis_ohne_versand); 
                   
                   }

                }
            }
            $html->clear();
            unset($$Mitbewerber_name_minPreis);
            unset($aktuell_AK_preis_ohne_versand);
            unset($min_preis_mitbewerber_versand);
            unset($min_preis_mitbewerber_versand_ohne_versand);
            unset($VK_AK_preis_versand);
            unset($DP_name_array);
            unset($neu_AK_preis_ohne_versand);
            unset($korrigieren);
            unset($store);
            unset($html);
            unset($Billigster);
            unset($aktuell_AK_preis);
            unset($DP_summe_preis_array);
            unset($ret_ja);
            unset($offer_price);
            unset($offer_name);
            unset($offer_versand_obj);
            unset($ret_auto_liefrate);
            unset($store);
            unset($ret_auto_liefrate);
            sleep(1);
            echo date('h:i:s') . "\n";
            echo "<br>After Call Memory Usage: ".memory_get_usage()."<br>";
			
    }
    rewind($output_preis_liste_handel);
    fclose($output_preis_liste_handel);
    rewind($Filter_output);
    fclose($Filter_output);
    $Vergleich_col=array('Artikel_Nummer','Autokrake_min_VK','Mitbewerber_min_Preis','Mitbewerber_min_Preis_Name','Autokrake_neue_Preis_Versand','Billigster?');
    $Besoders_Vergleich_col=array('Artikel_Nummer','Autokrake Preis ohne versand','Biligster Anbieter Name','Biligster Anbieter Preis');   
   
    build_csv($Herrsteller_Ordner,$Herrsteller_Name,'Besodres_Vergleich',$Besoders_Vergleich_col,$besoders_vergleich_liste); 
	build_csv($Herrsteller_Ordner,$Herrsteller_Name,'Vergleich',$Vergleich_col,$vergleich_liste);             
    build_csv($Herrsteller_Ordner,$Herrsteller_Name,'Korrektor','Null',$korriegern_liste_contetnt);
    unset($korriegern_liste_contetnt);
    unset($vergleich_liste);
}
?>