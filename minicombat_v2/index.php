<?php
require_once ('init.inc.php');

// vérification de l'envoi du formulaire
if (!empty ($_POST)){
    // debug ($_POST);

    // traitement d'une demande  de création d'un personnage à partir d'un nom donné
    if (isset($_POST['creer']) && !empty($_POST['nom']) && !empty($_POST['type'])){
        $nom = $_POST['nom'];
        $type = $_POST['type'];
        if ($pm -> create($nom, $type) != Personnage::SOI){
            $creer = 1;
        } else {
            $msg .= 'Le personnage ' . $nom . ' existe déjà !<br>';
        }

        // traitement d'un personnage qui va en frapper un autre
    }
    elseif (isset($_POST['utiliser']) && !empty($_POST['nom']) && !empty($_POST['type'])){
        $perso = $pm -> nomValide($_POST['nom'], $_POST['type']);
        if ($perso){
            if ($perso['type'] == "magicien"){
                $p1 = new Magicien ($perso);
            } else {
                $p1 = new Guerrier ($perso);
            }

            // $p1 = new Personnage ($perso);
            $persoEndormi = $p1 -> isEndormi();
            if ($persoEndormi){
                $msg .= "<p>" . $_POST['nom'] . " est endormi. Il se réveillera à " . $p1 -> dateReveil() . "</p>";
            }

            $utiliser = 1;
        } else {
            $msg .= "<p>Le " . $_POST['type'] . ' ' . $_POST['nom'] . " n'existe pas</p>";
        }
    } else {
        $msg .= "<p>Le formulaire est mal rempli</p> ";
    }

}

// cas du choix d'un personnage à frapper
if (empty($_POST) && isset($_GET['id1']) && isset($_GET['id2'])){
    // création d'un objet personnage
    $erreur = 0;
    $id1 = $_GET['id1'];
    if ($pm -> isExist($id1)){
        $arrayPerso = $pm -> read ($id1);
        if ($arrayPerso['type'] == "magicien"){
            $p1 = new Magicien ($arrayPerso);
        } else {
            $p1 = new Guerrier ($arrayPerso);
        }

    } else {
        $erreur = 1;
        $msg .= "<p>Erreur dans l'url</p>";
    }

    // création d'un objet personnage
    $id2 = $_GET['id2'];
    if ($pm -> isExist($id2)){
        $arrayPerso = $pm -> read ($id2);
        if ($arrayPerso['type'] == "magicien"){
            $p2 = new Magicien ($arrayPerso);
        } else {
            $p2 = new Guerrier ($arrayPerso);
        }
    } else {
        $erreur = 1;
        $msg .= "<p>Erreur dans l'url</p>";
    }

    if ($erreur == 0){
        $action = $_GET['action'];
        if ($action == "frappe"){
            $frappe = $p1 -> frapper ($p2);
            if ($frappe == Personnage::SOI){
                $msg .= "<p>Un personnage ne peut pas se frapper soi-même</p>";
            } else {
                $msg .= "<p>Le personnage " . $p2->getNom() . " a été frappé.<br>";
                if ($frappe == Personnage::FRAPPE){
                    $msg .= "Il a maintenant " . $p2->getDegats() . " dégats</p>";
                    $pm -> update ($p2);

                } else {
                    $msg .= "Cette frappe l'a tué</p>";
                    $pm -> delete ($p2 -> getId());
                }
            }
        }
        elseif ($action == "sort"){
            $sort = $p1 -> lancerSort ($p2);
            if ($sort == Personnage::SOI){
                $msg .= "<p>Un magicien ne peut pas s'ensorceler soi-même</p>";
            } else {
                $msg .= "<p>Le personnage " . $p2->getNom() . " a été endormi.<br>";
                $msg .= "Il se réveillera à " . $p2->dateReveil() . "</p>";
                $pm -> update ($p2);

            }
        }
        else{
            $erreur == 1;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Minicombat</title>
</head>
<body>
    <p><?= $msg ?></p>
    <?php if (!isset($utiliser) ) { ?>
        <?php $nbPerso = $pm -> nbPerso(); ?>
        <p>Nombre de personnages créés : <?= $nbPerso ?></p>

        <form action="" method="post">
            <label>Nom : </label>
            <input type="text" id="nom" name="nom">
            <select name="type">
                <option>guerrier</option>
                <option>magicien</option>
            </select>
            <input type="submit" name="creer" value="Créer ce personnage">
            <input type="submit" name="utiliser" value="Utiliser ce personnage">
        </form>
    <?php } elseif(!$persoEndormi) {
        $persos = $pm -> readAll();
        // debug($persos);
        ?>

        <p>Choisir un personnage à attaquer par <?= $p1 -> getNom() ?> : </p>
        <ul>
            <!-- liste des personnages -->
            <?php foreach ($persos as $perso) : ?>
                <li>
                    <a href="?id1=<?= $p1 -> getId() ?>&id2=<?= $perso['id'] ?>&action=frappe"><?= $perso['nom']?> </a><?= ' (Dégâts : '.$perso['degats'] . " | type : " . $perso['type'] . ") "?>
                    <a href="?id1=<?= $p1 -> getId() ?>&id2=<?= $perso['id'] ?>&action=sort"><?= ($p1 -> getType() == "magicien")?'Lancer un sort':'' ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php } ?>
</body>
</html>
