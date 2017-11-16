<?php
require_once ('Personnage.class.php');

class Guerrier extends Personnage
{
    public function recevoirDegats () {
        $this -> setDegats($this -> degats + 5 - $this -> atout);
        if ($this -> degats >= 100){
            return self::TUE;
        }
        return self::FRAPPE;
    }
}
