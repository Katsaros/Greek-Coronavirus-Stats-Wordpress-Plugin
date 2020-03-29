<?php
/**
 * Plugin Name: Greek Coronavirus Stats
 * Plugin URI: mrwebsite.gr
 * Donate link: https://ko-fi.com/katsaros
 * Description: Add the shortcode [greek-coronavirus-stats]
 * Version: 1.0
 * Text Domain: greek-coronavirus-stats
 * Author: Giannis Katsaros
 * Author URI: https://gr.linkedin.com/in/giannis-katsaros
 */

function load_coronavirus_stats_css() {
    wp_register_style('load_coronavirus_stats_css', plugins_url('/css/style.css',__FILE__ ));
    wp_enqueue_style('load_coronavirus_stats_css');
}
add_action( 'init','load_coronavirus_stats_css');

function compareByConfirmed($a, $b)
{
    return strnatcmp($a['1'], $b['1']);
}

function request_api_response()
{
	$url = "https://covid-api.quintessential.gr/data/";
	$client = curl_init($url);
	curl_setopt($client,CURLOPT_RETURNTRANSFER,true);
	$response = curl_exec($client);
	$result = json_decode($response,true);
    ?>
    <div class="container coronavirusstats">
        <center><h3>Στατιστικά Κορωνοϊού</h3></center>
        <div class="inputclassgrey">
            <div class="table-responsive">
                <table class="coronatable">
                    <tr>
                        <th>Χώρα</th>
                        <th>Επιβεβαιωμένα</th>
                        <th>Ανάρρωσαν</th>
                        <th>Θάνατοι</th>
                    </tr>
                    <?php 
                        $confirmedChina = 0;
                        $recoveredChina = 0;
                        $deathsChina = 0;
                        $chinaInserted = false;
                        
                        $countriesArray = array();
                        $countryArray = array();

                        foreach ($result as $key => $value) {
                            $country =  $value["Country/Region"];
                            if($country=="Greece" || $country=="Italy" || $country=="Spain" || $country=="Germany" || $country=="Turkey")
                            {
                                if($country=="Greece")$country="Ελλάδα";
                                if($country=="Italy")$country="Ιταλία";
                                if($country=="Spain")$country="Ισπανία";
                                if($country=="Germany")$country="Γερμανία";
                                if($country=="Turkey")$country="Τουρκία";
                                
                                $confirmed = $value["Confirmed"];
                                $recovered = $value["Recovered"];
                                $deaths = $value["Deaths"];
                                $lastUpdate = $value["Last Update"];
                                $lastUpdate = str_replace('T', ' στις ', $lastUpdate);
                                
                                array_push($countryArray, $country, $confirmed, $recovered, $deaths);
                                array_push($countriesArray, $countryArray);
                                unset($countryArray);
                                $countryArray = array();
                            }
                            else if ($country=="China")
                            {
                                if($chinaInserted==false)
                                {
                                    $countryChina="Κίνα";
                                    $confirmedChina += $value["Confirmed"];
                                    $recoveredChina += $value["Recovered"];
                                    $deathsChina += $value["Deaths"];
                                    unset($countryArray);
                                    $countryArray = array();
                                    $chinaInserted=true;
                                }
                                else
                                {
                                    $confirmedChina += $value["Confirmed"];
                                    $recoveredChina += $value["Recovered"];
                                    $deathsChina += $value["Deaths"];
                                }
                            }
                        }
                        
                        if($chinaInserted)
                        {
                            array_push($countryArray, $countryChina, $confirmedChina, $recoveredChina, $deathsChina);
                            array_push($countriesArray, $countryArray);
                        }
                        
                        usort($countriesArray, "compareByConfirmed");
                        
                        foreach ( $countriesArray as $var ) {
                            echo
                            "
                                <tr>
                                    <td><font style='color:black; font-weight: bold;'>".$var['0']."</font></td>
                                    <td><font style='color:grey; font-weight: bold;'>".$var['1']."</font></td>
                                    <td><font style='color:green; font-weight: bold;'>".$var['2']."</font></td>
                                    <td><font style='color:red; font-weight: bold;'>".$var['3']."</font></td>
                                </tr>
                            ";
                        }

                    ?>
                </table>
            </div>
        </div>
        <center><pre>Τελευταία Ενημέρωση Πίνακα:<br><?php echo $lastUpdate ?></pre></center>
    </div>
    <?php
}


function get_statistics_table( $atts )
{
    ob_start();
    request_api_response();
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
}

add_shortcode( 'greek-coronavirus-stats', 'get_statistics_table' );

?>