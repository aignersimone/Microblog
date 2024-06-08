<?php

class User
{
    //uRole 0 = nobody is Logged in (for the start)
    //uRole 1 = normal User
    //uRole 2 = admin User
public function __construct(public int $uID, public bool $uRole, public string $uName, public string $uEmail,
                            public string $password)
{ }
    public function __toString():string{
        $bResult = "<h2>The Users</h2><br>\n";
        $bResult .= "<p>UserID: " . $this->uID . "</p>";
        $bResult .= "<p>UserRole: " . ($this->uRole ? 'Admin' : 'Normal') . "</p>";
        $bResult .= "<p>UserName: " . $this->uName . "</p>";
        $bResult .= "<p>UserEmail: " . $this->uEmail . "</p>";
        return $bResult;
    }

}