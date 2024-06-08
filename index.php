<?php
session_name("swe4_kwm_hue05");
session_start();

spl_autoload_register(function ($sClassname) {
    require_once($sClassname.".php");
});
Database::loadConfig("config_inc.php");

error_reporting(E_ALL);ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SWE4: Hausuebung 03 + 04 + 05, Gruppe 1, Simone Aigner, s2210456002</title>
    <link rel="stylesheet" type="text/css" href="entry.css"/>
    <link rel="stylesheet" type="text/css" href="forms.css"/>
</head>
<body>


<?php
    $aUsers =[];
    $aErrors=[];


if(!isset($_SESSION["user"])){
    //case user is not logged in
    if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "login"){
        //case user wants to log in
        if(isset($_REQUEST["username"]) && isset($_REQUEST["password"]) &&
            loginDataCorrect($_REQUEST["username"], $_REQUEST["password"])){
            //case user data is correct

            $_SESSION["user"] = array("username" => $_REQUEST["username"],
                "userid" => getUserId($_REQUEST["username"]), "userrole" => getUserRole($_REQUEST["username"]));

            showNewEntryForm();
            showBlogEntries();
            showLogoutForm();
        }
        else{
            //case user data is incorrect
            echo("<p class='info'>Login data incorrect!</p><br>");
            showLoginForm();
            showRegistrationForm();
            showBlogEntries();
        }
    }
    else if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "register"){
        //case user wants to register
        if (isset($_REQUEST["usernamereg"]) && usernameAvailable($_REQUEST["usernamereg"]) && checkRegistration() && register($_REQUEST["usernamereg"], $_REQUEST["email"], $_REQUEST["password1"], $_REQUEST["password2"]))
        {
            echo "Successfully registered! You can now login with the username <i>".$_REQUEST["usernamereg"]."</i><br/>";
            showLoginForm();
            showRegistrationForm();
            showBlogEntries();
        }else{
            //Data not correct
            //var_dump($aErrors);
            echo("<p class='info'>Registration data not correct!</p><br>");
        }
        //User wants to view page
        showLoginForm();
        showRegistrationForm();
        showBlogEntries();
    }
    else{
        //case user (not logged in) only wants to view page
        showLoginForm();
        showRegistrationForm();
        showBlogEntries();
    }
}else{
    //case user is logged in
    if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "logout"){
        //case user wants to log out
        echo("<p class='info'>You have been logged out! </p><br>");
        unset($_SESSION["user"]);
        showLoginForm();
        showRegistrationForm();
        showBlogEntries();
    }
    else if(isset($_REQUEST["action"]) && $_REQUEST["action"] =="create") {
        //user wants to create a task
        if (isset($_REQUEST["title"]))
        {
            if (entryTitleAvailable($_REQUEST["title"]))
            {
                //echo "title is available <br/>";
                if (createEntry($_REQUEST["title"], $_SESSION["user"]["userid"]))
                {
                    echo "Blogentry was successfully created!<br/>";
                }
                else
                {
                    echo "Could not create blogentry.<br/>";
                }
            }
            else
            {
                echo "This blogentry name is not available!<br/>";
            }
        }
        showNewEntryForm();
        showBlogEntries();
        showLogoutForm();

    }  else{
        //case user only wants to view page
        //Delete Button implementieren
        if(isset($_REQUEST["action"]) && $_REQUEST["action"] === "Delete"){
            //echo("Delete button clicked");
            if(isset($_REQUEST["entryid"])) {
                $entry_id = $_REQUEST["entryid"];
                //echo("Delete button clicked for entry with ID: " . $entry_id);
                deleteEntry($entry_id);
            } else {
                echo("Error: Entry ID not found.");
            }
        }
        //Edit Button implementieren
        if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "Edit"){
            if(isset($_REQUEST["entryid"])) {
                $entry_id = $_REQUEST["entryid"];
                //echo("Update button clicked for entry with ID: " . $entry_id);
                showEditEntryForm($entry_id);
            } else {
                echo("Error: Entry ID not found.");
            }
        }
        if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "update"){
            if(isset($_REQUEST["entryid"])) {
                $entry_id = $_REQUEST["entryid"];
                changeEntry($entry_id);
            }
        }
        //Highlight Button implementieren
        if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "Highlight"){
            if(isset($_REQUEST["entryid"])) {
                $entry_id = $_REQUEST["entryid"];
                //echo("Delete button clicked for entry with ID: " . $entry_id);
                changeHighlight($entry_id);
            } else {
                echo("Error: Entry ID not found.");
            }
        }
        showNewEntryForm();
        showBlogEntries();
        showLogoutForm();
    }
}

function showEditEntryForm($sEntryID) {
    $entry = null;
    if ($sEntryID) {
        $sSelectQuery = "SELECT * FROM blogentry WHERE entryID = '" . $sEntryID . "';";
        $mResult = Database::selectQuery($sSelectQuery);
        if ($mResult) {
            $row = $mResult->fetch_assoc();
            $entry = $row;
        }
    }
    ?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="content">
            <h3>Edit Entry:</h3>
            <div class="row">
                <label for="title">Title: </label>
                <input type="text" name="changetitle" id="changetitle" value="<?= $entry ? $entry['entryTitle'] : '' ?>">
            </div>
            <div class="row">
                <label for="content">Content: </label>
                <textarea name="changecontent" id="changecontent" rows="7" cols="22"><?= $entry ? $entry['entryText'] : '' ?></textarea>
            </div>
            <div class="row">
                <input type='hidden' name='entryid' value='<?= $sEntryID ?>'>
                <input type="hidden" name="action" value="update">
                <input type="submit" value="Update">
            </div>
        </form>
    <?php
}

function changeEntry($sEntryID) {
    $sSelectQuery = " SELECT entryUserID FROM blogentry WHERE entryID = '".$sEntryID."';";
    $mResult = Database::selectQuery($sSelectQuery);
    if ($mResult) {
        $row = $mResult->fetch_assoc();
        $sUserID = $row['entryUserID'];
    }
    if($_SESSION["user"]["userrole"] == 2 || $_SESSION["user"]["userid"] == $sUserID) {
        $changerID = $_SESSION["user"]["userid"];
        $changeDate = date("Y-m-d H:i:s");
        $sTitle = isset($_REQUEST["changetitle"]) ? $_REQUEST["changetitle"] : " ";
        $sEntryContent = isset($_REQUEST["changecontent"]) ? $_REQUEST["changecontent"] : " ";

        if(entryTitleAvailable($sTitle)){
            // SQL-Update-Statement vorbereiten
            $sUpdateQuery = "UPDATE blogentry SET entryTitle = '" . $sTitle . "', entryText = '" . $sEntryContent . "', changerID = '" . $changerID . "', changeDate = '" . $changeDate . "' WHERE entryID = '" . $sEntryID . "';";
        } else{
            $sUpdateQuery = "UPDATE blogentry SET entryText = '" .$sEntryContent . "', changerID = '" . $changerID . "', changeDate = '" . $changeDate . "' WHERE entryID = '" . $sEntryID . "';";
        }

        // Das Update-Statement ausführen
        $isUpdated = Database::updateQuery($sUpdateQuery);

        // Überprüfen, ob das Update erfolgreich war
        if ($isUpdated) {
            echo "Eintrag mit ID $sEntryID erfolgreich geändert.";
        } else {
            echo "Fehler beim Ändern des Eintrags mit ID $sEntryID.";
        }
    }else{
        return;
    }
}
function showBlogEntries(): void
{
    $sSelectQuery = "SELECT * FROM blogentry";
    $mResult = Database::selectQuery($sSelectQuery);

    $oBlog = new MyMicroBlog();

    // wenn erfolgreich
    if ($mResult) {
        while ($row = $mResult->fetch_assoc()) {
            // blog entry objekt erstellen
            $blogEntry = new MyMicroBlogEntry(
                $row['entryID'],
                new DateTime($row['entryDate']),
                $row['entryUserID'], // Use createFromFormat for date
                $row['entryTitle'],
                $row['changeDate'] ? new DateTime($row['changeDate']) : null,
                $row['changerID'] ? $row['changerID'] : null,
                $row['entryHighlight'],
                $row['entryText']
            );

            // drucken des entries
            $oBlog->addEntry($blogEntry);
        }
        echo $oBlog;
    } else {
        echo "Error: Database query failed.";
    }
}
function changeHighlight(string $sEntryID) {
    $sSelectQuery = " SELECT entryUserID FROM blogentry WHERE entryID = '".$sEntryID."';";
    $mResult = Database::selectQuery($sSelectQuery);
    if ($mResult) {
        $row = $mResult->fetch_assoc();
        $sUserID = $row['entryUserID'];
    }
    if($_SESSION["user"]["userrole"] == 2 || $_SESSION["user"]["userid"] == $sUserID) {

        $changerID = $_SESSION["user"]["userid"];
        $changeDate = date("Y-m-d H:i:s");

        // SQL-Update-Statement vorbereiten
        $sUpdateQuery = "UPDATE blogentry SET entryHighlight = 'true', changerID = '" . $changerID . "', changeDate = '" . $changeDate . "' WHERE entryID = '" . $sEntryID . "';";

        // Das Update-Statement ausführen
        $isUpdated = Database::updateQuery($sUpdateQuery);

        // Überprüfen, ob das Update erfolgreich war
        if ($isUpdated) {
            echo "Eintrag mit ID $sEntryID erfolgreich hervorgehoben.";
        } else {
            echo "Fehler beim Hervorheben des Eintrags mit ID $sEntryID.";
        }
    }else{
        return;
    }
}
function deleteEntry(string $sEntryID){
    $sSelectQuery = " SELECT entryUserID FROM blogentry WHERE entryID = '".$sEntryID."';";
    $mResult = Database::selectQuery($sSelectQuery);
    if ($mResult) {
        $row = $mResult->fetch_assoc();
        $sUserID = $row['entryUserID'];
    }
    if($_SESSION["user"]["userrole"] == 2 || $_SESSION["user"]["userid"] == $sUserID) {

        $sDeleteQuery = "DELETE FROM blogentry WHERE entryID = '".$sEntryID."';";
        $isDeleted = Database::deleteQuery($sDeleteQuery);
        if ($isDeleted) {
            echo "Eintrag mit ID $sEntryID erfolgreich gelöscht.";
        } else {
            echo "Fehler beim Löschen des Eintrags mit ID $sEntryID.";
        }
    }else{
        return;
    }
}
function showNewEntryForm() {
    if( $_SESSION["user"]["userrole"] == 2){
        return;
    }else{
        global $aErrors;
        $sTitle ="";
        $sContent ="";

        if(isset($_REQUEST["title"]) && count($aErrors) >0){
            $sTitle = $_REQUEST["title"];
        }
        if(isset($_REQUEST["content"]) && count($aErrors) > 0){
            $sContent = $_REQUEST["content"];
        }

        ?>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="content">
            <h3>New Entry:</h3>
            <div class="row">
                <label for="title">Title: </label>
                <?php
                if(isset($aErrors["title"])){
                    ?> <input type="text" name="title" id="title" class="error" value="<?php echo $sTitle; ?>"> <?php
                    echo("<span class='info'>".$aErrors["title"]."</span> ");
                } else{
                    ?> <input type="text" name="title" id="title" value="<?php echo $sTitle; ?>"> <?php
                }
                ?>

            </div>
            <div class="row">
                <label for="content">Content: </label>
                <textarea name="content" id="content" rows="7" cols="22"><?php echo $sContent; ?></textarea>
            </div>
            <div class="row">
                <label for="highlighted">Highlighted: </label>
                <input type="checkbox" name="highlighted" id="highlighted">
            </div>
            <div class="row">
                <input type="hidden" name="action" value="create">
                <input type="submit" value="Create">
            </div>
        </form>
        <?php
    }
}
function entryTitleAvailable(string $sEntryTitle)
{
    $sSelectQuery = "SELECT entryTitle FROM blogentry WHERE entryTitle='".$sEntryTitle."';";
    $mResult = Database::selectQuery($sSelectQuery);

    if ($mResult->num_rows > 0)
    {
        return false;
    }
    return true;
}
function createEntry(string $sEntryTitle, string $sEntryOwnerID){
    $sEntryOwnerID = intval($sEntryOwnerID);
    $sHighlight = isset($_REQUEST["highlighted"]) ? 'true' : 'false';
    $sEntryContent = isset($_REQUEST["content"]) ? $_REQUEST["content"] : " ";

    $sEntryDate =  new DateTime(date("Y-m-d H:i:s"));

    $sInsertQuery = "INSERT INTO blogentry (entryDate, entryUserID, entryTitle, entryText, entryHighlight) 
    VALUES ('".$sEntryDate->format("Y-m-d H:i:s")."', '".$sEntryOwnerID."', '".$sEntryTitle."', '".$sEntryContent."', '".$sHighlight."');";
    $iEntryID = Database::insertQuery($sInsertQuery);

    if ($iEntryID != 0) {
        return true;
    }
    return false;
}
function showLoginForm(){
    global $aErrors;
    ?>

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="logreg">
        <label for="username">Username: </label>

        <input <?php if(isset($aErrors['login_username'])) echo 'class="error"';?> type="text" name="username" id="username" value="<?php if (isset($_REQUEST['username'])) echo $_REQUEST['username']; ?>">
        <?php if (isset($aErrors['login_username']))
        {
            echo "<span class=\"error\"'>".$aErrors['login_username']." </span>";
        }?>
        <label for="password">Password: </label>
        <input <?php if(isset($aErrors['login_password'])) echo 'class="error"';?> type="password" name="password" id="password">
        <?php
        if (isset($aErrors['login_password']))
        {
            echo "<span class=\"error\">".$aErrors['login_password']."</span>";
        }
        ?>
        <input type="hidden" name="action" value="login">
        <input type="submit" value="Login">





    </form>
<?php }
function loginDataCorrect(string $sUsername, string $sPassword): bool {
    global $aErrors;

    $sSelectQuery = "SELECT * FROM bloguser WHERE userName='".$sUsername."';";
    $mResult = Database::selectQuery($sSelectQuery);


    // Überprüfen, ob die Abfrage ein Ergebnis geliefert hat
    if (($mResult) && $mResult->num_rows > 0) {
        $aResult = mysqli_fetch_assoc($mResult);

        if (password_verify($sPassword, $aResult['userPassword']))
        {
            return true;
        }
        $aErrors['login_password'] = "Password is not correct";
        return false;
    }
    else
    {
        $aErrors["login_username"] = "Username does not exist in DB!";
        return false;
    }
}
function getUserID($sUsername){
    $sSelectQuery = "SELECT userID FROM bloguser WHERE userName='".$sUsername."';";

    $mResult = Database::selectQuery($sSelectQuery);
    if ($aRow = $mResult->fetch_assoc())
    {
        return $aRow["userID"];
    }
    else return null;
}
function getUserRole($sUsername){
    $sSelectQuery = "SELECT userRole FROM bloguser WHERE userName='".$sUsername."';";

    $mResult = Database::selectQuery($sSelectQuery);
    if ($aRow = $mResult->fetch_assoc())
    {
        return $aRow["userRole"];
    }
    else return null;
}
function showRegistrationForm(){
    global $aErrors;
    $sUsername ="";
    $sMail ="";
    $sPassword1 = $sPassword2 = "";

    if(isset($_REQUEST["usernamereg"]) && count($aErrors) >0){
        $sUsername = $_REQUEST["usernamereg"];
    }
    if(isset($_REQUEST["email"]) && count($aErrors) > 0){
        $sMail = $_REQUEST["email"];
    }
    if(isset($_REQUEST["password1"]) && count($aErrors) >0){
        $sPassword1 = $_REQUEST["password1"];
    }
    if(isset($_REQUEST["password2"]) && count($aErrors) > 0){
        $sPassword2 = $_REQUEST["password2"];
    }
    ?>

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="logreg">
        <table>
            <tr>
                <td><label for="usernamereg">Username:</label></td>
                <td>
                    <?php if(isset($aErrors["usernamereg"])): ?>
                        <input type="text" name="usernamereg" id="usernamereg" class="error" value="<?php echo $sUsername; ?>">
                        <span class='info'><?php echo $aErrors["usernamereg"]; ?></span>
                    <?php else: ?>
                        <input type="text" name="usernamereg" id="usernamereg" value="<?php echo $sUsername; ?>">
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td><label for="password1">Password:</label></td>
                <td>
                    <?php if(isset($aErrors["password1"])): ?>
                        <input type="password" name="password1" id="password1" value="<?php echo $sPassword1; ?>" class="error">
                        <span class='info'><?php echo $aErrors["password1"]; ?></span>
                    <?php else: ?>
                        <input type="password" name="password1" id="password1">
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td><label for="password2">Repeat Password:</label></td>
                <td>
                    <?php if(isset($aErrors["password2"])): ?>
                        <input type="password" name="password2" id="password2" value="<?php echo $sPassword2; ?>" class="error">
                        <span class='info'><?php echo $aErrors["password2"]; ?></span>
                    <?php else: ?>
                        <input type="password" name="password2" id="password2">
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td><label for="email">Email:</label></td>
                <td>
                    <?php if(isset($aErrors["email"])): ?>
                        <input type="email" name="email" id="email" class="error" value="<?php echo $sMail; ?>">
                        <span class='info'><?php echo $aErrors["email"]; ?></span>
                    <?php else: ?>
                        <input type="email" name="email" id="email" value="<?php echo $sMail; ?>">
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td><label for="admin">I am an Admin:</label></td>
                <td><input type="checkbox" name="admin" id="admin"></td>
            </tr>
        </table>
        <input type="hidden" name="action" value="register">
        <input type="submit" value="Register">
    </form>

<?php }
function usernameAvailable(string $sUsername):bool{
    $sSelectQuery = "SELECT userName FROM bloguser WHERE userName='".$sUsername."';";

    $mResult = Database::selectQuery($sSelectQuery);
    if ($mResult->num_rows > 0)
    {
        return false;
    }
    return true;
}
function checkRegistration():bool{
    //auch hier hat man Zugriff auf das Request Array, daher muss man sie nicht mit rein geben
    global $aErrors;
    //username
    if(!isset($_REQUEST["usernamereg"]) || strlen($_REQUEST["usernamereg"]) < 5){
        $aErrors["usernamereg"] = "Username must be at least 5 characters long!";
    }
    //pw
    if(!isset($_REQUEST["password1"]) || !isset($_REQUEST["password2"]) ||
        $_REQUEST["password1"] != $_REQUEST["password2"] || strlen($_REQUEST["password1"]) < 8 ){
        $aErrors["password1"] = "Password must be at least 8 characters long and passwords must match!";
        $aErrors["password2"] = "Password must be at least 8 characters long and passwords must match!";
    }
    //email
    if(!isset($_REQUEST["email"]) || filter_var($_REQUEST["email"], FILTER_VALIDATE_EMAIL) === false){
        $aErrors["email"] = "Email must be a valid email adress!";
    }
    if(count($aErrors) > 0){
        return false;
    }else{
        return true;
    }
}
function register($sUsername, $sEmail, $sPassword1){
    global $aUsers;
    $isAdmin = isset($_REQUEST['admin']) ? 2 : 1;

    $sInsertQuery = "INSERT INTO bloguser (userRole, userName, userPassword, userEmail) VALUES('"
        .$isAdmin."', '"
        .$sUsername."', '"
        .password_hash($sPassword1, PASSWORD_DEFAULT)."', '"
        .$sEmail."');";
    $iID = Database::insertQuery($sInsertQuery);
    if ($iID != 0)
    {
        $user = new User($iID ,$isAdmin, $sUsername, $sPassword1, $sEmail);
        $aUsers[] = $user;
        return true;
    }
    return false;
}
function showLogoutForm(){ ?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="logreg">
        <input type="hidden" name="action" value="logout">
        <input type="submit" value="Logout">
    </form>
<?php }
?>
</body>
</html>
