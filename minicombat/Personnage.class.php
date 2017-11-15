<?php

class Personnage
{
    const TUE = 0;
    const SOI = 1;
    const FRAPPE = 2;
    private $id;
    private $nom;
    private $degats;

    public function __construct(array $data){
        $this -> hydrate($data);
    }

    public function hydrate(array $data){
        foreach ($data as $key => $val){
            $method = 'set'.ucfirst($key);
            if (method_exists($this, $method)){
                $this->$method($val);
            }
        }

    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $id = (int) $id;
        if ($id > 0)
        $this->id = $id;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function setNom($nom)
    {
        if (is_string($nom))
            $this -> nom = $nom;

    }

    public function getDegats()
    {
        return $this -> degats;
    }

    public function setDegats($degats)
    {
        $degats = (int) $degats;
        if ($degats >=0 && $degats <= 100){
            $this -> degats = $degats;
        }
    }

    public function frapper (Personnage $perso){
        if ($perso -> getId() == $this -> getId()){
            return self::SOI;
        }
        return $perso -> recevoirDegats ();
    }

    public function recevoirDegats () {
        $this -> setDegats($this -> getDegats() + 5);
        if ($this -> getDegats() >= 100){
            return self::TUE;
        }
        return self::FRAPPE;
    }

}
