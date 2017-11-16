<?php
require_once ('init.inc.php');
require_once ('Personnage.class.php');

class PersonnageManager
{
    private $bdd;

    function __construct($db)
    {
        $this->bdd = $db;
    }

    public function getBdd()
    {
        return $this->bdd;
    }

    public function setBdd($bdd)
    {
        $this->bdd = $bdd;

        return $this;
    }

    public function create ($nom, $type){
        $personnage = $this -> nomValide($nom);
        if (!$personnage){
            $bd = $this -> getBdd();
            $req = $bd -> prepare ("INSERT INTO personnages_v2 (nom, type) VALUES (:nom, :type)");
            $req -> bindParam(':nom', $nom, PDO::PARAM_STR);
            $req -> bindParam(':type', $type, PDO::PARAM_STR);
            $req -> execute ();
        }
        else {
            return Personnage::SOI;
        }


    }

    public function update (Personnage $perso){
        $bd = $this -> getBdd();
        $req = $bd -> prepare ("UPDATE personnages_v2 SET degats = :degats, timeEndormi = :timeEndormi WHERE id = :id");
        $id = $perso -> getId();
        $degats = $perso -> getDegats();
        $timeEndormi = $perso -> getTimeEndormi();
        $req -> bindParam(':id', $id, PDO::PARAM_INT);
        $req -> bindParam(':degats', $degats, PDO::PARAM_INT);
        $req -> bindParam(':timeEndormi', $timeEndormi, PDO::PARAM_INT);
        $req -> execute();
    }

    public function delete ($id){
        $bd = $this -> getBdd();
        $req = $bd -> prepare ("DELETE FROM personnages_v2 WHERE id = :id");
        $req -> bindParam(':id', $id, PDO::PARAM_INT);
        $req -> execute();
    }

    public function read ($id){
        $bd = $this -> getBdd();
        $req = $bd -> prepare ("SELECT * FROM personnages_v2 WHERE id = :id");
        $req -> bindParam(':id', $id, PDO::PARAM_INT);
        $req -> execute();
        $personnage = $req -> fetch(PDO::FETCH_ASSOC);
        return $personnage;
    }

    public function isExist ($id){
        $bd = $this -> getBdd();
        $result = $bd -> prepare ("SELECT * FROM personnages_v2 WHERE id = :id");
        $result -> bindParam(':id', $id, PDO::PARAM_INT);
        $result -> execute ();
        if ($result -> rowCount() > 0){
            return true;
        }
        return false;
    }

    public function readAll (){
        $bd = $this -> getBdd();
        $result = $bd -> query ("SELECT * FROM personnages_v2");
        $personnages = $result -> fetchAll(PDO::FETCH_ASSOC);
        return $personnages;
    }

    public function nbPerso (){
        $bd = $this -> getBdd();
        $result = $bd -> query ("SELECT * FROM personnages_v2");
        return $result -> rowCount();
    }

    public function nomValide($nom, $type){
        $bd = $this -> getBdd();
        $result = $bd -> prepare ("SELECT * FROM personnages_v2 WHERE nom = :nom AND type = :type");

        $result -> bindParam(':nom', $nom, PDO::PARAM_STR);
        $result -> bindParam(':type', $type, PDO::PARAM_STR);
        $result -> execute ();
        if ($result -> rowCount()){
            return $result -> fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }
}
