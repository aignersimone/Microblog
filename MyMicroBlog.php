<?php

class MyMicroBlog
{
    //Konstruktor definieren
    public function __construct(private array $bEntries =[])
    { }

    //Einen Blogeintrag in das Array hinzufügen
    public function addEntry(MyMicroBlogEntry $bEntry):void{
        array_push($this->bEntries, $bEntry);
    }

    //Einen Blogeintrag aus dem Array entfernen
    public function deleteEntry(MyMicroBlogEntry $blogEntry):void{
        foreach ($this->bEntries as $key => $entry) {
            if ($entry === $blogEntry) {
                unset($this->bEntries[$key]);
                return;
            }
        }
    }

    //Titel und Text eines Entry ändern
    public function changeEntry(MyMicroBlogEntry $blogEntry, $newTitle, $newText):void{
        foreach ($this->bEntries as $key => $entry) {
            if ($entry === $blogEntry) {
                $entry->bTitle = $newTitle;
                $entry->bText = $newText;
                return;
            }
        }
    }

    //Einen Blogeintrag aus dem Array highlighten
    public function highlightEntry(MyMicroBlogEntry $blogEntry):void{
        foreach ($this->bEntries as $key => $entry) {
            if ($entry === $blogEntry) {
                $entry->setStatusToTrue();
                return;
            }
        }
    }

    //Blogeinträge nacheinander ausgeben als div's
    public function __toString():string{
        $bResult = "<h2>Welcome to MicroBlog</h2><br>\n <div class='blog'>\n";
        foreach($this->bEntries as $bEntry){
            $bResult.=$bEntry;
        }
        $bResult.="</div>\n";
        return $bResult;
    }


}