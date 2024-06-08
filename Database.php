<?php

class Database
{
    public static $oMysqli;

    public static function loadConfig(String $sConfigFile){
        require_once ($sConfigFile);

    }

    public static function deleteQuery(string $sQuery):bool{
        if(Database::connect()){
            $mResult = Database::$oMysqli->query($sQuery);
            Database::disconnect();
            return $mResult; //result only tells us if the SQL Statement could be processed and not if something was actually deleted
        }else{
            echo "Could not connect in deleteQuery!";
            return false;
        }
    }

    public static function insertQuery(string $sQuery):int{
        if(Database::connect()){
            $mResult = Database::$oMysqli->query($sQuery);
            $iID = Database::$oMysqli->insert_id;
            Database::disconnect();
            return $iID; //return ID of new inserted row
        }
        else{
            echo "Could not connect in insertQuery!";
            return 0;
        }
    }

    public static function selectQuery(string $sQuery):mysqli_result{
        if (Database::connect()){
            $mResult = Database::$oMysqli->query($sQuery);
            Database::disconnect();
            return $mResult; //A mysqli result object --> later fetch assoc
        }
        else{
            echo "Could not connect in selectQuery!";
            return null;
        }
    }

    public static function updateQuery(string $sQuery): bool {
        if(Database::connect()) {
            $mResult = Database::$oMysqli->query($sQuery);
            Database::disconnect();
            return $mResult;
        } else {
            echo "Could not connect in updateQuery!";
            return false;
        }
    }

    private static function connect():bool{
        Database::$oMysqli = @new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

        if(Database::$oMysqli ->connect_error){
            die ("Could not establish database connection: ".Database::$oMysqli->connect_error);
            return false;
        }
        return true;
    }

    private static function disconnect(){
        if (Database::$oMysqli != null){
            Database::$oMysqli->close();
        }
//        if (!Database::$oMysqli->close())
//        {
//            echo "Cold not close db connection!<br/>";
//        }
    }


}