<?php
function debug($var){
    echo '<pre>';
    print_r ($var);
    echo '</pre>';
}

require_once ('Guerrier.class.php');
require_once ('Magicien.class.php');
// require_once ('Personnage.class.php');

require_once ('PersonnageManager.class.php');

// connexion à la BDD
$bdd = new PDO("mysql:host=localhost;dbname=oc-php-poo", 'root' , '',
array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
));

// initialisation de la variable $msg qui contiendra les éventuels messages d'erreur à afficher
$persoEndormi = false;
$msg = '';

// création de l'objet $pm qui permet de gérer la table personnage
$pm = new PersonnageManager($bdd);
