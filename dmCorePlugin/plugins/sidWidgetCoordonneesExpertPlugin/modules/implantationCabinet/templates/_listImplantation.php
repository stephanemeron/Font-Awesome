<?php
// $vars : $adresses, $titreBloc
//echo dm_get_widget('dmWidgetGoogleMap','show', array('$adress' => 2));
echo _tag('h2.title', $titreBloc);
$i = 1;
$i_max = count($adresses);
$class = '';
$html = '';
echo _open('ul.elements');

    foreach($adresses as $adresse){
    // condition pour gérer les class des listings
    if ($i == 1) {
        $class = ' first';
        if ($i == $i_max)
                $class = ' first last';
    }
    elseif ($i == $i_max)
        $class = ' last';
    else
        $class = '';
    // condition pour gérer les class des listings
    $html = _tag('span.wrapper', 
                _tag('span', array('class' => 'subWrapper'), 
                    _tag('span', array('class' => 'title itemprop name', 'itemprop' => 'name'),$adresse->getTitle())
                        ).
                _tag('span', array('class' => 'teaser itemprop description', 'itemprop' => 'description'), $adresse->getResumeTown())
                );
    
    echo _open('li', array('class' => 'element '.$class));
        echo _link($adresse)->text($html)->set('.link_box');
    echo _close('li');
    $i++;
    $html='';
    }

echo _close('ul');    