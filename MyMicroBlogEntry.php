<?php

class MyMicroBlogEntry
{
    //Konstruktor definieren
    public function __construct(private int    $bID, private DateTime $bCreate,
                                private string    $uIDCreator, private string $bTitle,
                                private ?DateTime $bLastChange = null, private ?string $uIDChange = null,
                                private string      $bStatus = 'false', private string $bText ='')
    { }

    //Setter festlegen
    public function __set(string $bName, mixed $bValue){
        if(property_exists('MyMicroBlogEntry', $bName)) {
            $this->{$bName} = $bValue;
        }
    }

    //einen Eintrag wohl formatiert ausgeben
    public function __toString():string{
        if($this->bStatus == 'true'){
            $bResult="<div class='container-entry highlighted'>";
        } else{
            $bResult="<div class='container-entry normal'>";
        }
        //nur wenn der Entry dem aktuell angemeldeten User entspricht, wird dieser in einer anderen Farbe dargestellt
        if(isset($_SESSION["user"])){
            if($_SESSION["user"]["userid"] == $this->uIDCreator ){
                //echo($_SESSION["user_id"]);
                $bResult="<div class='container-entry userEntry'>";
            }
        }
        $bResult.= "<p><b>Entry id:</b> ".$this->bID." by User ".$this->uIDCreator."</p>\n";
        $bResult.="<p><b>Created:</b> ".$this->bCreate->format("Y-m-d H:i:s")." <b>Title:</b> ".$this->bTitle;
        if($this->bText !== '') {
            $bResult.=" </p><p><b>Note:</b> ".$this->bText;
        }
        $bResult.=" </p>\n";
        if($this->bLastChange != null || $this->uIDChange != null) {
            $bResult.="<p><i><b>Last edited by </b> User".$this->uIDChange." at ".$this->bLastChange->format("Y-m-d H:i:s")."</i></p>\n";
        }

        //wenn aktuell ein User angemeldet ist und dieser entweder Admin ist, oder der aktuelle Entry seiner UserID entspricht
        //werden die Buttons zum Editieren, Löschen und Highlighten angezeigt
        if(isset($_SESSION["user"])){
            /*echo($_SESSION["user_id"]);*/
            if(isset($_SESSION["user"]["userid"]) && isset($_SESSION["user"]["userrole"])) {
                //echo($_SESSION["user_id"]);
                //echo($_SESSION["user_role"]);
                //echo($this->uIDCreator);
                if ($_SESSION["user"]["userid"] == $this->uIDCreator || $_SESSION["user"]["userrole"] == 2) {
                    //echo($_SESSION["user_role"]);
                    //echo($_SESSION["user_id"]);
                    $bResult .= "<table class='button-group'>";
                    $bResult .= "<tr>";
                    $bResult .= "<td><form action='" . $_SERVER['PHP_SELF'] . "' method='post'><input type='hidden' name='entryid' value='" . $this->bID . "'><input type='hidden' name='action' value='Edit'><input type='submit' value='Edit'></form></td>";
                    $bResult .= "<td><form action='" . $_SERVER['PHP_SELF'] . "' method='post'><input type='hidden' name='entryid' value='" . $this->bID . "'><input type='hidden' name='action' value='Delete'><input type='submit' value='Delete'></form></td>";
                    $bResult .= "<td><form action='" . $_SERVER['PHP_SELF'] . "' method='post'><input type='hidden' name='entryid' value='" . $this->bID . "'><input type='hidden' name='action' value='Highlight'><input type='submit' value='Highlight'></form></td>";
                    $bResult .= "</tr>";
                    $bResult .= "</table><br>";
                }
            }
        }


        $bResult.="</div>\n<br>\n";
        return $bResult;
    }

    //Den Status von bStatus auf true setzten = zum highlighten
    public function setStatusToTrue(){
        $this->bStatus = true;
    }


}