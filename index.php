<?php
/**
 * Created by a.rytikov << chlp
 * Date/Time: 09.07.13/3:09
 */

header('Content-Type: text/html; charset=utf-8');
error_reporting(~0);
ini_set('display_errors', 1);

$myPlace = 'Рудневка 25';

include('simple_html_dom.php');

$html = new simple_html_dom();

// TODO: foreach all pages
$html->load_file('http://www.avito.ru/moskva?name=macbook');

$tiers = $html->find('.t_i_e_r');
foreach($tiers as $key=>$tier)
{
    $error = false;

    // TODO: read container and fields from $_POST. Add variables type, sort type.
    $tier->find('h3.t_i_h3', 0)->find('a', 0)->href = 'http://avito.ru' . $tier->find('h3.t_i_h3', 0)->find('a', 0)->href;
    $array[$key]['title'] = $tier->find('h3.t_i_h3', 0)->innertext;
    if(count($tier->find('div.t_i_data', 0)->children()) > 1)
    {
        $array[$key]['place'] = trim($tier->find('div.t_i_data', 0)->children(1)->plaintext);
        if($array[$key]['place'])
        {
            $resp = json_decode(file_get_contents('http://maps.googleapis.com/maps/api/distancematrix/json?origins=' . urlencode($myPlace) . '&destinations=' . urlencode($array[$key]['place']) . '&sensor=false'), true);
            if($resp['status'] == "OK")
            {
                $distance[$key] = $resp['rows'][0]['elements'][0]['distance']['value'];
                $array[$key]['distance'] = $resp['rows'][0]['elements'][0]['distance']['value'];
            }
            else
            {
                $error = true;
            }
        }
        else
        {
            $error = true;
        }
    }
    else
    {
        $error = true;
    }

    if($error)
    {
        unset($array[$key]);
    }
}

asort($distance);

echo '<table>';
foreach($distance as $key=>$value)
{
    echo '<tr>'
        .'  <td>' . $array[$key]['title'] . '</td>'
        .'  <td>' . $array[$key]['distance'] . '</td>'
        .'  <td>' . $array[$key]['place'] . '</td>'
        .'</tr>';
}
echo '</table>';