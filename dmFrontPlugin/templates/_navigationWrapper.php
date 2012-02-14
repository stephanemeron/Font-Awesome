<?php
/*
 * _navigationWrapper.php
 * v1.4
 * Permet d'afficher une navigation de page (à améliorer avec gestion intégrée des tableaux
 * 
 * Variables disponibles :
 * $placement	top ou bottom	
 * $elements	liens de navigation à afficher (html)
 * $pager		pager de type diem (html)
 * $container	container de l'ensemble
 * 
 */

//Configuration par défaut

//container par défaut
if(!isset($container)) $container = 'div';

//déclaration des propriétés par défaut du container
$ctnOpts = array('class' => array('navigationWrapper'));
switch ($placement) {
	case 'top':
		$ctnOpts['class'][] = 'navigationTop';
		break;
	case 'bottom':
		$ctnOpts['class'][] = 'navigationBottom';
		break;
	default:
		break;
}

//Composition de la sortie html
$html = '';

//liens de navigation multiples
if(isset($elements)){
	$html.= _open('ul.elements');

	//compteur
	$count = 0;
	$maxCount = count($elements);

	foreach ($elements as $element) {
		//incrémentation compteur
		$count++;
		
		//options de l'élément
		$elementOpt = array();
		
		//gestion de l'index de positionnement
		if($count == 1)			$elementOpt['class'][] = 'first';
		if($count >= $maxCount)	$elementOpt['class'][] = 'last';
		
		//création du lien
		$htmlLink = _link($element['linkUrl'])->text($element['title'])->title($element['title']);
		//ajout de l'ancre si présente
		if(isset($element['anchor'])) $htmlLink->anchor($element['anchor']);
		
		//insertion du lien dans un li
		$html.= _tag('li.element', $elementOpt, $htmlLink);
	}

	$html.= _close('ul.elements');
}

//Pager par défaut de Diem de type 1,2,3,4,5
if(isset($pager)){
	//on génère le html du pager
	switch ($placement) {
		case 'top':
			$htmlPager = $pager->renderNavigationTop();
			break;
		case 'bottom':
			$htmlPager = $pager->renderNavigationBottom();
			break;
		default:
			$htmlPager = null;
			break;
	}
	
	//on insère le pager que si le html résultant n'est pas vide
	if($htmlPager != null) $html.= _tag('div.pager', $htmlPager);
}

//englobage dans un container
$html = _tag($container, $ctnOpts, $html);

//affichage html en sortie
echo $html;