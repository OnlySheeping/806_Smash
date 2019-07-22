<?php 
// Julian Jacquez
// INEW 2334 001
// Last edited: 05/7/2019
// UpcomingTile Class

declare(strict_types = 1);

class UserInterface
{
    public $tourneys;
    public $events;
    
    public function makeUpcomingTile($tournaments)
    {
        

        $test = "test";
        $this->tourneys = $tournaments;
        for($i=0; $i<count($this->tourneys); $i++){
            $dateTime = date("F j, Y - g a", intval($this->tourneys[$i][6]));
            $tile = "";
            $tile .= "<article class='masonry__brick entry format-standard' data-aos='fade-up'>";
            $tile .= "<div class='entry__thumb'>";
            //$tile .= "<a href='single-standard.html' class='entry__thumb-link'>";
            $tile .= "<img src='images/gamecube.jpg' alt=''>";
            $tile .= "</a>";
            $tile .= "</div>";
            $tile .= "<div class='entry__text'>";
            $tile .= "<div class='entry__header'>";
            $tile .= "<div class='entry__date'>";
            $tile .= "<a href='single-standard.html'>". $dateTime ."</a>";
            $tile .= "</div>";
            $tile .= "<h1 class='entry__title'><a href='single-standard.html'>". $this->tourneys[$i][1] ."</a></h1>";
            $tile .= "</div>";
            $tile .= "<div class='entry__excerpt'>";
            $tile .= "<p>". $this->tourneys[$i][2] ."</p>";
            $tile .= "</div>";
            $tile .= "<div class='entry__meta'>";
            $tile .= "<span class='entry__meta-links'>";

            $tile .= "</span>";
            $tile .= "</div>";
            $tile .= "</div>";
            $tile .= "</article>";
            print($tile);
        }
        
    }

    //Creates a tourney result tile
    public function makePastTile(Array $events)
    {
        $tournament = new Tournament();
        $this->events = $events;
        //var_dump($this->events);
        for($i=0; $i<count($this->events); $i++){
            $tourneys = $tournament->getTourneyById($this->events[$i][4]);
            //$dateTime = date("F j, Y : g a", intval($this->events[$i][6]));
            $tile = "";
            $tile .= "<article class='masonry__brick entry format-standard' data-aos='fade-up'>";
            $tile .= "<div class='entry__thumb'>";
            //$tile .= "<a href='single-standard.html' class='entry__thumb-link'>";
            $tile .= "<img src='images/gamecube.jpg' alt=''>";
            $tile .= "</a>";
            $tile .= "</div>";
            $tile .= "<div class='entry__text'>";
            $tile .= "<div class='entry__header'>";
            $tile .= "<div class='entry__date'>";
            $tile .= "<a href='single-standard.html'>". $tourneys[1] ."</a>";
            $tile .= "</div>";
            $tile .= "<h1 class='entry__title'>";
            $tile .= $this->events[$i][5] . "<br>";
            $tile .= "1st : " . $this->events[$i][1] . "<br>";
            $tile .= "2nd : " . $this->events[$i][2] . "<br>";
            $tile .= "3rd : " . $this->events[$i][3] . "<br>";
            $tile .= "</h1>";
            $tile .= "</div>";
            $tile .= "<div class='entry__excerpt'>";
            $tile .= "<p>". $tourneys[3] ."</p>";
            $tile .= "</div>";
            $tile .= "<div class='entry__meta'>";
            $tile .= "<span class='entry__meta-links'>";
            $tile .= "</span>";
            $tile .= "</div>";
            $tile .= "</div>";
            $tile .= "</article>";
            print($tile);
        }
        
        
    }
}
?>