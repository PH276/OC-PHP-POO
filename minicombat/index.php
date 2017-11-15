<?php
require_once ('init.inc.php');

// vérification de l'envoi du formulaire
if (!empty ($_POST)){
    // debug ($_POST);

    // traitement d'une demande  de création d'un personnage à partir d'un nom donné
    if (isset($_POST['creer']) && !empty($_POST['nom'])){
        $nom = $_POST['nom'];
        if ($pm -> create($nom) != Personnage::SOI){
            $creer = 1;
        } else {
            $msg .= 'Le personnage ' . $nom . ' existe déjà !<br>';
        }

        // traitement d'un personnage qui va en frapper un autre
    }
    elseif (isset($_POST['utiliser']) && !empty($_POST['nom'])){
        $perso = $pm -> nomValide($_POST['nom']);
        if ($perso){
            $p1 = new Personnage ($perso);
            $utiliser = 1;
        } else {
            $msg .= "<p>" . $_POST['nom'] . " n'existe pas</p>";
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
        $p1 = new Personnage ($arrayPerso);
    } else {
        $erreur = 1;
        $msg .= "<p>Erreur dans l'url</p>";
    }

    // création d'un objet personnage
    $id2 = $_GET['id2'];
    if ($pm -> isExist($id2)){
        $arrayPerso = $pm -> read ($id2);
        $p2 = new Personnage ($arrayPerso);
    } else {
        $erreur = 1;
        $msg .= "<p>Erreur dans l'url</p>";
    }

    if ($erreur == 0){

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
            <input type="submit" name="creer" value="Créer ce personnage">
            <input type="submit" name="utiliser" value="Utiliser ce personnage">
        </form>
    <?php } else {
        $persos = $pm -> readAll();
        // debug($persos);
        ?>

        <p>Choisir un personnage à frapper par <?= $p1 -> getNom() ?> : </p>
        <ul>
            <!-- liste des personnages -->
            <?php foreach ($persos as $perso) : ?>
                <li>
                    <a href="?id1=<?= $p1 -> getId() ?>&id2=<?= $perso['id'] ?>"><?= $perso['nom'].' '.$perso['degats'] ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php } ?>
</body>
</html>
