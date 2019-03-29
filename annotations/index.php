<?php
// On commence par inclure les fichiers nécessaires.
require 'addendum/annotations.php';
require 'MyAnnotations.php';
require 'Personnage.class.php';

$reflectedClass = new ReflectionAnnotatedClass('Personnage');
