<?php
require_once ('Personnage.class.php');

class Magicien extends Personnage
{
    public function lancerSort ($perso){
        if ($perso -> getId() == $this -> getId()){
            return self::SOI;
        }
        return $perso -> setTimeEndormi(time() + $this -> atout * 6 * 3600);
    }
}
