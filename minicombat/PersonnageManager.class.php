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

    public function create ($perso){
        $personnage = $this -> nomValide($perso);
        if (!$personnage){
            $bd = $this -> getBdd();
            $req = $bd -> prepare ("INSERT INTO personnages (nom) VALUES (:nom)");
            $req -> bindParam(':nom', $perso, PDO::PARAM_STR);
            $req -> execute ();
        }
        else {
            return Personnage::SOI;
        }


    }

    public function update (Personnage $perso){
        $bd = $this -> getBdd();
        $req = $bd -> prepare ("UPDATE personnages SET degats = :degats WHERE id = :id");
        $id = $perso -> getId();
        $degats = $perso -> getDegats();
        $req -> bindParam(':id', $id, PDO::PARAM_INT);
        $req -> bindParam(':degats', $degats, PDO::PARAM_INT);
        $req -> execute();
    }

    public function delete ($id){
        $bd = $this -> getBdd();
        $req = $bd -> prepare ("DELETE FROM personnages WHERE id = :id");
        $req -> bindParam(':id', $id, PDO::PARAM_INT);
        $req -> execute();
    }

    public function read ($id){
        $bd = $this -> getBdd();
        $req = $bd -> prepare ("SELECT * FROM personnages WHERE id = :id");
        $req -> bindParam(':id', $id, PDO::PARAM_INT);
        $req -> execute();
        $personnage = $req -> fetch(PDO::FETCH_ASSOC);
        return $personnage;
    }

    public function isExist ($id){
        $bd = $this -> getBdd();
        $result = $bd -> prepare ("SELECT * FROM personnages WHERE id = :id");
        $result -> bindParam(':id', $id, PDO::PARAM_INT);
        $result -> execute ();
        if ($result -> rowCount() > 0){
            return true;
        }
        return false;
    }

    public function readAll (){
        $bd = $this -> getBdd();
        $result = $bd -> query ("SELECT * FROM personnages");
        $personnages = $result -> fetchAll(PDO::FETCH_ASSOC);
        return $personnages;
    }

    public function nbPerso (){
        $bd = $this -> getBdd();
        $result = $bd -> query ("SELECT * FROM personnages");
        return $result -> rowCount();
    }

    public function nomValide($nom){
        $bd = $this -> getBdd();
        $result = $bd -> prepare ("SELECT * FROM personnages WHERE nom = :nom");

        $result -> bindParam(':nom', $nom, PDO::PARAM_STR);
        $result -> execute ();
        if ($result -> rowCount()){
            return $result -> fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }
}
