<?php

require_once("../vendor/autoload.php");

class GQLConnection
{
    public function CallAPI($method, $url)
    {
        $curl = curl_init();
        
        
//         $rawData = array("query" => 'query TournamentsByCountry($cCode: String!, $perPage: Int!) {
//     tournaments(query: {
//         perPage: $perPage
//         filter: {
//             countryCode: $cCode
//         }
//     }) {
//         nodes {
//             id,
//             name,
//             countryCode,
//         }
//     }
// }',
//             "variables" => '{
//     "cCode": "JP",
//     "perPage": 4
// }');
        $rawData = array("query" => 'query TournamentsByState($cordinates: String! $radius: String!) {
            tournaments(query: {
                filter: {
                    location: {
                        distanceFrom: $cordinates,
                        distance: $radius
                    }
                }
               }) {
                nodes {
                id
                name
                addrState
                city
                contactPhone
                mapsPlaceId
                venueAddress
                postalCode
                events {
                    videogameId
                    startAt
                }
      
              }
          }
        },',
            "variables" => '{
                "cordinates":  "35.2220, -101.8313",
                "radius":  "200mi"
            }'
        );
        $data = json_encode($rawData);
        $header = [
            "Content-Type: application/json; charset=utf-8",
            "Content-Length: " . strlen($data),
            "Authorization: Bearer " . "91aec6b18f34af536caf745ef3ebb87f"
        ];
        print("<pre>");
        print_r($header);
        print_r($data);
        print("</pre>");
        curl_setopt($curl, CURLOPT_URL, $url);
        //curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        
        $result = curl_exec($curl);
        //print_r($result);
        //print_r($curl);
        //print(curl_getinfo($curl, CURLINFO_RESPONSE_CODE ));
        if ($result === false) {
            $info = curl_getinfo($curl);
            print("</pre>");
            print_r($info);
            print("</pre>");
            curl_close($curl);
            die('error occured during curl exec. Additioanl info: ' . var_export($info));
        }
        //print(curl_getinfo($curl, CURLINFO_RESPONSE_CODE ));
        curl_close($curl);
        return $result;
    }
}

$gqlConnection =  new GQLConnection();
$data = $gqlConnection->CallAPI("POST", "https://api.smash.gg/gql/alpha");
$dataDecode = json_decode($data, true);
//print_r($dataDecode);
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Example Frontend that is a REST Consumer</title>
    </head>
    <body>
        <table>
        <?php 
        //print($dataDecode);
        print_r($data);
        //for($i = 0; $i < count($data); $i++) {
        //    print("<tr>");
        //    foreach($data[$i] as $key => $val) {
        //        if($key != "ID") {
        //            print("<td>" . $val . "</td>");
        //        }
        //    }
        //    print("</tr>");
        //}
        ?>
        </table>
    </body>
</html>