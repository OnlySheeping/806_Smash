<?php 
// Julian Jacquez
// INEW 2334 001
// Last edited: 05/08/2019
// Tournament Class

declare(strict_types = 1);
require_once 'ApiConnection.php';

class Tournament
{
    public $tourneyId;
    public $tourneyName;
    public $address;
    public $city;
    public $phone;
    public $postalCode;
    public $startTime;
    
    private $dbConnection;
    
    //Set values for a Upcoming Tournament
    public function setUpcoming(
        int $tourneyId,
        String $tourneyName,
        String $address,
        String $city,
        String $phone,
        String $postalCode,
        int $startTime
    ): void {
        $this->tourneyId = $tourneyId;
        $this->tourneyName = $tourneyName;
        $this->address = $address;
        $this->city = $city;
        $this->phone = $phone;
        $this->postalCode = $postalCode;
        $this->startTime = $startTime;
    }
    //Set values for a past tournament
    private function setPast(
        int $tourneyId,
        String $tourneyName,
        String $address,
        String $city,
        String $phone,
        String $postalCode
    ): void {
            $this->tourneyId = $tourneyId;
            $this->tourneyName = $tourneyName;
            $this->address = $address;
            $this->city = $city;
            $this->phone = $phone;
            $this->postalCode = $postalCode;
    }
    private function establishDbConnection()
    {
        $databaseName = "806_smash";
        $password = "";
        $server = "localhost";
        $username = "root";
        
        $this->dbConnection = new mysqli($server,
            $username,
            $password,
            $databaseName);
        
        if ($this->dbConnection->connect_errno) {
            echo "Failed to connect to MySQL: " . $this->dbConnection->connect_error;
        }
    }
    //Insert any amount of tourneys
    public function insertUpcomingTourney()
    {
        //Get upcoming tourneys from the api
        $apiConnection =  new ApiConnection();
        $tourneys = $apiConnection->apiGetUpcomingTourneys();
        
        //Nodes equal to tournament nodes array
        $nodes = $tourneys["data"]["tournaments"]["nodes"];
        
        // Establish DB connection
        $this->establishDbConnection();
        
        // Prepare insert SQL statement
        $sql = "INSERT into `Tournament` (`tourneyId`,`tourneyName`, `address`, `city`, `phone`, `postalCode`, `startTime`) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $preparedStatement = null;
        if(!$preparedStatement = $this->dbConnection->prepare($sql)) {
            print($preparedStatement->error);
        }
        
        //Check for existing tourneys
        $existingTournaments = $this->getTourneysIds();
        
        //Add each tourney node to the database
        foreach($nodes as $node){
            
            //Check to see if the current Tournament node has already been added to the DB
            if(in_array($node["id"], $existingTournaments)) continue;
            
            //bind params
            if(!$preparedStatement->bind_param("isssssi", $node["id"], $node["name"], $node["venueAddress"], $node["city"], $node["contactPhone"], $node["postalCode"], $node["startAt"])) {
                print($preparedStatement->error);
            }
            
            // Execute your bound and prepared statement
            if(!$preparedStatement->execute()) {
                print($preparedStatement->error);
            }
        }
        $this->dbConnection->close();
    }
    public function insertPastTourney()
    {
        $apiConnection =  new ApiConnection();
        $tourneys = $apiConnection->apiGetPastTourneys();
        
        //Nodes equal to tournament nodes array
        $nodes = $tourneys["data"]["tournaments"]["nodes"];
        
        // Establish DB connection
        $this->establishDbConnection();
        
        //Check DB for already recorded tournaments
        $existingTournaments = $this->getTourneysIds();
                
        // Prepare insert SQL statement
        $sql = "INSERT into `Tournament` (`tourneyId`, `tourneyName`, `address`, `city`, `phone`, `postalCode`, `startTime`) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $preparedStatement = null;
        if(!$preparedStatement = $this->dbConnection->prepare($sql)) {
            print($preparedStatement->error);
        }
        
        //Check for existing tourneys
        $existingTournaments = $this->getTourneysIds();
        
        
        //Add each tourney node to the database
        foreach($nodes as $node){

            //Check to see if the current Tournament node has already been added to the DB
            if(in_array($node["id"], $existingTournaments)) continue;
            
            
            //bind params
            if(!$preparedStatement->bind_param("isssssi", $node["id"], $node["name"], $node["venueAddress"], $node["city"], $node["contactPhone"], $node["postalCode"], $node["startAt"])) {
                print($preparedStatement->error);
            }
            
            // Execute your bound and prepared statement
            if(!$preparedStatement->execute()) {
                print($preparedStatement->error);
            }
            
            $this->insertEvent($node["id"]);
        }
        $this->dbConnection->close();
    }
    
    //Insert an event into the DB
    public function insertEvent($tourneyId)
    {
        //Get events and standings from api
        $apiConnection =  new ApiConnection();
        $standings = $apiConnection->apiGetStandings($tourneyId);
        
        // Establish DB connection
        //$this->establishDbConnection();
        
        
        // Prepare insert SQL statement
        $sql = "INSERT into `Event` (`eventId`, `firstPlace`, `secondPlace`, `thirdPlace`, `tourneyId`, `eventName`) VALUES (?, ?, ?, ?, ?, ?)";
        
        $preparedStatement = null;
        if(!$preparedStatement = $this->dbConnection->prepare($sql)) {
            print($preparedStatement->error);
        }
        
        print("<pre>");
        var_dump($standings);
        print("<pre>");
        
        //Loop through apiGetstandings array
        for($i=1; $i<count($standings); $i+=2)
        {
            
            $eventId =  $standings[$i-1];
            $eventName = $standings[$i]["data"]["event"]["name"];
            $first = $standings[$i]["data"]["event"]["standings"]["nodes"][0]["entrant"]["name"];
            $second = $standings[$i]["data"]["event"]["standings"]["nodes"][1]["entrant"]["name"];
            $third = $standings[$i]["data"]["event"]["standings"]["nodes"][2]["entrant"]["name"];
            
            //bind params
            if(!$preparedStatement->bind_param("isssis", $eventId, $first, $second, $third, $tourneyId, $eventName)) {
                print($preparedStatement->error);
            }
            
            // Execute your bound and prepared statement
            if(!$preparedStatement->execute()) {
                print($preparedStatement->error);
            }
            //$this->dbConnection->close();
        }
    }
    
    //Pull all Tournaments ids from the DB
    private function getTourneysIds() :array
    {
        $sql = "SELECT tourneyId FROM tournament";
        $check = $this->dbConnection->query($sql);
        $existingTournaments = array();
        if (mysqli_num_rows($check) > 0) {
            // output data of each row
            while($row = $check->fetch_assoc()) {
                //echo "id: " . $row["id"]. " - Name: " . $row["firstname"]. " " . $row["lastname"]. "<br>";
                array_push($existingTournaments, $row["tourneyId"]);
            }
        }
        return $existingTournaments;
    }
    
    //Get Events tournaments from the DB
    public function getEvents() :array
    {
        // Establish DB connection
        $this->establishDbConnection();
        
        $sql = "SELECT * FROM event";
        
        $check = $this->dbConnection->query($sql);
        
        $this->dbConnection->close();
        
        $existingEvents = array();
        
        if (mysqli_num_rows($check) > 0) {
            // output data of each row
            while($row = $check->fetch_assoc()) {
                $currentTourney = $this->getTourneyById($row["tourneyId"]);
                $temp = array();
                array_push($temp, $row["eventId"]);
                array_push($temp, $row["firstPlace"]);
                array_push($temp, $row["secondPlace"]);
                array_push($temp, $row["thirdPlace"]);
                array_push($temp, $row["tourneyId"]);
                array_push($temp, $row["eventName"]);
                array_push($existingEvents, $temp);
            }
        }
        
        return $existingEvents;
    }
    
    //Get Upcoming tournaments from the Db
    public function getUpcoming() :array
    {
        // Establish DB connection
        $this->establishDbConnection();
        
        $sql = "SELECT * FROM tournament";
        
        $check = $this->dbConnection->query($sql);
        
        $existingTournaments = array();
        
        if (mysqli_num_rows($check) > 0) {
            // output data of each row
            while($row = $check->fetch_assoc()) {
                
                if($row["startTime"] == null) continue;
                $temp = array();
                array_push($temp, $row["tourneyId"]);
                array_push($temp, $row["tourneyName"]);
                array_push($temp, $row["address"]);
                array_push($temp, $row["city"]);
                array_push($temp, $row["phone"]);
                array_push($temp, $row["postalCode"]);
                array_push($temp, $row["startTime"]);
                array_push($existingTournaments, $temp);
            }
        }
        $this->dbConnection->close();
        
        return $existingTournaments;
    }
    
    public function getTourneyById($tourneyId)
    {
        // Establish DB connection
        $this->establishDbConnection();
        
        $sql = "SELECT * FROM tournament";
        
        $check = $this->dbConnection->query($sql);
        
        $existingTournaments = array();
        
        if (mysqli_num_rows($check) > 0) {
            // output data of each row
            while($row = $check->fetch_assoc()) {
                
                if($row["tourneyId"] == $tourneyId){
                    array_push($existingTournaments, $row["tourneyId"]);
                    array_push($existingTournaments, $row["tourneyName"]);
                    array_push($existingTournaments, $row["address"]);
                    array_push($existingTournaments, $row["city"]);
                    array_push($existingTournaments, $row["phone"]);
                    array_push($existingTournaments, $row["postalCode"]);
                    array_push($existingTournaments, $row["startTime"]);
                    
                }
                
            }
        }
        //var_dump($existingTournaments);
        $this->dbConnection->close();
        
        return $existingTournaments;
    }
    
}

//$apiConnection =  new ApiConnection();
//$dataTwo = $apiConnection->apiGetStandings(149161);

// $tourney =  new Tournament();
// $data = $tourney->insertUpcomingTourney();
// $data = $tourney->getUpcoming();
//var_dump($stuff);
//$dataDecode = json_decode($data, true);
//print_r($dataDecode);
// ?>
<!DOCTYPE html>
<!-- <html> -->
<!--     <head> -->
<!--         <title>Tournament Test</title> -->
<!--     </head> -->
<!--     <body> -->
<!--         <pre> -->
        <?php 
//         var_dump($data);
//         ?>
<!--         </pre> -->
<!--     </body> -->
<!-- </html> -->
