<?php
// Julian Jacquez
// INEW 2334 001
// Last edited: 04/22/2019
// APIConnection Class

declare(strict_types=1);
require_once("../vendor/autoload.php");

class APIConnection
{
    // Sets the options for the cURL request to the API
    private function apiSetOptions($query) : String
    {
        $curl = curl_init();
        $data = json_encode($query);
        
        //Retrieve token from config.json
        $token = file_get_contents("../config.json");
        $tokenDecode = json_decode($token, true);
        
        $header = [
            "Content-Type: application/json; charset=utf-8",
            "Content-Length: " . strlen($data),
            "Authorization: Bearer " . $tokenDecode["api/token"],
        ];
        curl_setopt($curl, CURLOPT_URL, "https://api.smash.gg/gql/alpha");
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        
        $result = curl_exec($curl);
        
        if ($result === false) {
            $info = curl_getinfo($curl);
            print("</pre>");
            print_r($info);
            print("</pre>");
            curl_close($curl);
            die('error occured during curl exec. Additioanl info: ' . var_export($info));
        }
        curl_close($curl);
        return $result;
    }
    
    //Gets Upcoming Tournaments from the api
    public function apiGetUpcomingTourneys() : array
    {
        $query = array("query" => 'query TournamentsByLocation($cordinates: String! $radius: String! $videogameId: ID!) {
            tournaments(query: {
                
                filter: {
                  upcoming: true
                  videogameIds: [
                    $videogameId
                  ]
                    location: {
                        distanceFrom: $cordinates,
                        distance: $radius
                    }
                }
               }) {
                nodes {
                id
                name
                city
                postalCode
                contactPhone
                venueAddress
                startAt
              }
          }
        },',
            "variables" => '{
                "videogameId": 1386,
                "cordinates":  "35.2220, -101.8313",
                "radius":  "200mi"
            }'
        );
        $result = $this->apiSetOptions($query);
        $resultDecode = json_decode($result, True);
        return $resultDecode;
    }
    
    //Get past tournaments from the api
    public function apiGetPastTourneys() : array
    {
        $query = array("query" => 'query TournamentsByLocation($cordinates: String! $radius: String! $videogameId: ID!) {
            tournaments(query: {
            
                filter: {
                  past: true
                  videogameIds: [
                    $videogameId
                  ]
                    location: {
                        distanceFrom: $cordinates,
                        distance: $radius
                    }
                }
               }) {
                nodes {
                id
                name
                city
                postalCode
                contactPhone
                venueAddress
              }
          }
        },',
            "variables" => '{
                "videogameId": 1386,
                "cordinates":  "35.2220, -101.8313",
                "radius":  "200mi"
            }'
        );
        $result = $this->apiSetOptions($query);
        $resultDecode = json_decode($result, True);
        return $resultDecode;
        
    }
    
    //Gets Tournament standings (First,Second and Third) from a specific tournament using the tourneyId
    public function apiGetStandings(Int $tourneyId) :array
    {   
        //Get the eventid from apiGetTournaments and store it in $eventId
        $data = $this->apiGetEventIds($tourneyId);
        $nodes = $data["data"]["tournaments"]["nodes"][0]["events"];
        
        $eventIds = array();
        foreach($nodes as $node){
            if($node["videogameId"] == 1386){
                array_push($eventIds, $node["id"]);
            }
        }
        //Check to see if eventId is empty
        if($eventIds == null){
            $eventId = "No SBU Events found.";
            return $eventId;
        }
        
        $standings = array();
        //get each evenid standing and return them
        foreach($eventIds as $eventId){
            $query = array("query" => 'query EventStandings($eventId: ID!, $page: Int!, $perPage: Int!) {
            event(id: $eventId) {
                name
                standings(query: {
                    perPage: $perPage,
                    page: $page
                }){
                    nodes {
                        placement
                        entrant {
                            name
                            }
                        }
                    }
                }
            },',
                "variables" => '{
                "eventId": '. $eventId . ',
                "page": 1 ,
                "perPage": 3
            }'
            );
            
            $result = $this->apiSetOptions($query);
            $resultDecode = json_decode($result, True);
            array_push($standings, $eventId);
            array_push($standings, $resultDecode);
            
        }
        
        return $standings;

    }
    //Get tournament event ids
    private function apiGetEventIds(Int $tourneyId): array
    {
        $query = array("query" => 'query TournamentsById($ID: ID!) {
            tournaments(query: {
                perPage: 1
                
                filter: {
                    id: $ID,
                }
            }) {
                nodes {
                    events{
                        id
                        videogameId
                    }
                    
                }

            }
        },',
        "variables" => '{
            "ID": '. $tourneyId .'
        }');
        $result = $this->apiSetOptions($query);
        $resultDecode = json_decode($result, True);
        return $resultDecode;
    }

}