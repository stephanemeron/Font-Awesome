<?php

echo _tag('h4.title','Calcul des frais kilométriques : véhicules automobiles');

echo $form.'<hr style="clear:both; visibility:hidden;">';

if ($sf_user->hasFlash('results')) {

    $results = $sf_user->getFlash('results');

    //var_dump($results); // pour afficher les valeurs postée et le résultat soap

    echo $results['soap'];
    
}