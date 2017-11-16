<?php

class Personnage
{
    const TUE = 0;
    const SOI = 1;
    const FRAPPE = 2;
    protected $id;
    protected $nom;
    protected $degats;
    protected $atout;
    protected $type;
    protected $timeEndormi;

    public function __construct(array $data){
        $this -> hydrate($data);
        $this->type = strtolower(static::class);
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
        $this -> setDegats($this -> degats + 5);
        if ($this -> degats >= 100){
            return self::TUE;
        }
        return self::FRAPPE;
    }

    public function getAtout()
    {
        return $this->atout;
    }

    public function setAtout()
    {
        $degats = $this -> getAtout();
        $this->atout = 0;
        if ($degats < 90){
            $this->atout = 4 - floor($degats/25);
        }
    }

    public function getType()
    {
        return $this->type;
    }

    // public function setType($type)
    // {
    //
    //     $this->type = $type;
    // }
    //
    public function getTimeEndormi()
    {
        return $this->timeEndormi;
    }

    public function setTimeEndormi($timeEndormi)
    {
        $this->timeEndormi = $timeEndormi;
    }

    public function isEndormi(){
        if ($this -> timeEndormi > time()){
            return true;
        }
        return false;
    }

    public function dateReveil(){
        $te = $this -> timeEndormi;
        return date ('H', $te ) . ' heures, '
        . date ('i', $te ) . ' minutes, '
        . date ('s', $te ) . ' secondes, ';
    }
}
