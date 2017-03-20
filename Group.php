<?php
/*
Copyright (c) 2017 Joey Sabey

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/
require_once "User.php";

class Group {
    private const SQL_GROUPS_SCHEMA = "sql/groups.sql";
    private const SQL_GROUP_MEMBERS_SCHEMA = "sql/groupMembers.sql";

    public const GET_BY_ID   = 0;
    public const GET_BY_NAME = 1;

    protected $id          = NULL;
    protected $name        = NULL;
    protected $description = NULL;

    public function getID() : int {
        return $this->id;
    }
    public function getName() : string {
        return $this->name;
    }
    public function getDescription() : string {
        return $this->description;
    }
    public function getUsers() : array {
        $users = [];
        $sql = 'SELECT userID FROM groupMembers WHERE groupID = :g';
        $query = User::getDB()->prepare($sql);
        $query->bindValue(':g', $this->id, PDO::PARAM_INT);
        $query->execute();
        foreach($query->fetchAll(PDO::FETCH_ASSOC) as $row)
            $users[] = new User($row['userID'], User::GET_BY_ID);
        return $users;
    }

    public function setName(string $name) : void {
        //TODO: Validate
        $sql = 'UPDATE groups SET name=:n WHERE id=:i';
        $query = User::getDB()->prepare($sql);
        $query->bindValue(':n', $name, PDO::PARAM_STR);
        $query->bindValue(':i', $this->id, PDO::PARAM_INT);
        $query->execute();
    }

    public function setDescription(string $desc) : void {
        //TODO: Validate
        $sql = 'UPDATE groups SET description=:d WHERE id=:i';
        $query = User::getDB()->prepare($sql);
        $query->bindValue(':d', $desc, PDO::PARAM_STR);
        $query->bindValue(':i', $this->id, PDO::PARAM_INT);
        $query->execute();
    }

    public function __toString() : string {
        return $this->name;
    }

    public function __construct($uid, $getType = self::GET_BY_ID) {
        if($getType == static::GET_BY_ID) {
            $query = User::getDB()->prepare('SELECT * FROM groups WHERE id = :i');
            $query->bindValue(':i', $uid, PDO::PARAM_INT);
        }
        else if($getType == static::GET_BY_NAME) {
            $query = User::getDB()->prepare('SELECT * FROM groups WHERE name = :n');
            $query->bindValue(':n', $uid, PDO::PARAM_STR);
        }
        else
            throw new Exception('Uh-oh!'); //TODO: better exception
        $query->execute();
        $query->bindColumn('id', $this->id, PDO::PARAM_INT);
        $query->bindColumn('name', $this->name, PDO::PARAM_STR);
        $query->bindColumn('description', $this->description, PDO::PARAM_STR);
        $query->fetch(PDO::FETCH_BOUND);
        if($this->id === NULL)
            throw new Exception('Also uh-oh!'); //TODO: better exception
    }

    public function addUser(User $u) : void {
        //TODO: duplicate check?
        $sql = 'INSERT INTO groupMembers(userID, groupID) VALUES(:u, :g);';
        $query = User::getDB()->prepare($sql);
        $query->bindValue(':u', $u->getID(), PDO::PARAM_INT);
        $query->bindValue(':g', $this->id, PDO::PARAM_INT);
        $query->execute();
    }

    public function removeUser(User $u) : void {
        $sql = 'DELETE FROM groupMembers WHERE userID = :u AND groupID = :g';
        $query = User::getDB()->prepare($sql);
        $query->bindValue(':u', $u->getID(), PDO::PARAM_INT);
        $query->bindValue(':g', $this->id, PDO::PARAM_INT);
        $query->execute();
    }

    public function containsUser(User $u) : bool {
        $sql = 'SELECT COUNT (*) FROM groupMembers WHERE userID = :u AND groupID = :g';
        $query = User::getDB()->prepare($sql);
        $query->bindValue(':u', $u->getID(), PDO::PARAM_INT);
        $query->bindValue(':g', $this->id, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchColumn() != 0;
    }

    public function remove() : void {
        $sql = 'DELETE FROM groups WHERE id = :g';
        $query = User::getDB()->prepare($sql);
        $query->bindValue(':g', $this->id, PDO::PARAM_INT);
        $query->execute();
    }

    public static function add(string $name, ?string $desc = NULL) : void {
        //TODO: Validate
        $sql = 'INSERT INTO groups(name, description) VALUES(:n, :d)';
        $query = User::getDB()->prepare($sql);
        $query->bindValue(':n', $name, PDO::PARAM_STR);
        $query->bindValue(':d', $desc, PDO::PARAM_STR);
        $query->execute();
    }

    public static function setupDB() : void {
        User::setupDB();
        static::executeSQL(static::SQL_GROUPS_SCHEMA);
        static::executeSQL(static::SQL_GROUP_MEMBERS_SCHEMA);
    }

    protected static function executeSQL(string $file) : void {
        $db = User::getDB();
        $sql = file_get_contents($file);
        $db->exec($sql);
    }
}

?>
