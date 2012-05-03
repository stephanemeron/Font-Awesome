// frontTemplate.js
// v1.2
// Last Updated : 2012-05-02 15:20
// Copyright : SID Presse
// Author : Arnaud GAUDIN

//permet d'isoler le code du reste de l'environnement javascript
(function($){

	//Définition du plugin
	$.fn.frontTemplate = function() {

		// iterate and reformat each matched element
		return this.each(function() {
			//Affichage debug initialisation
			$.fn.frontFramework.debug("frontTemplate maestroTheme | initialisation");

			//Configuration du redimenssionnement
			$.fn.frontTemplate.resizeCols({
										offsetHC: 0,
										offsetHSL: 0,
										offsetHSR: 0
										});
		});
	}

	//Gestion de la taille des colonnes
	$.fn.frontTemplate.resizeCols = function (options) {

		//changement des valeurs des options
		//on vérifie la présence de différents widgets à placer en bas dans la sidebarLeft
		/*
		//sélection de diverses widgets à placer en bas de la colonne
		// var widgetFollowingMenu = $('#dm_sidebar_left > .dm_zones > .dm_zone > .dm_widgets > .dm_widget.navigation_menu ~ .dm_widget:not(.navigation_menu)');
		// notation ne nécessitant pas de parser, donc plus rapide
		// cf. http://aahacreative.com/2010/07/19/jquery-fastest-method-find-descendents
		var widgetFollowingMenu = $('#dm_sidebar_left').find('.dm_widget.navigation_menu').siblings('.dm_widget').not('.navigation_menu');

		//initialisation hauteur totale
		var fullHeight = 0;

		//on parcourt la sélection de widgets
		widgetFollowingMenu.each(function(index) {
			//incrémentation hauteur courante (marge comprise)
			fullHeight += $(this).outerHeight(true);

			//replacement en absolu du widget
			$(this).css({
						'position' : 'absolute',
						'bottom' : (fullHeight*-1)
					});
			
			// $.fn.frontFramework.debug(index + " widget : " + $(this).attr('class') + ' fullHeight ' + fullHeight + ' outerHeight ' + $(this).outerHeight(true));
		});

		//on ajoute du padding bottom à la sidebar pour loger les widgets précisemment, et on change le position des zones
		$('#dm_sidebar_left').css('paddingBottom', fullHeight).children('.dm_zones').css('position', 'relative');

		//on soustrait la hauteur au calcul à posteriori
		options.isPostHSL = true;
		options.offsetHSL -= fullHeight;
		// $.fn.frontFramework.debug("widgetFollowingMenu fullHeight : " + fullHeight);
		*/

		//on rajoute l'espace manquant en bas à droite à priori afin de combler le manque éventuel
		options.offsetHC += $('#dm_main').height() - $('#dm_main_inner').height();

		//on calcul la hauteur de la zone customBottom et de sa sous-zone à gauche
		var customBottomHeight = $('#dm_custom_bottom').outerHeight(true);
		var customBottomLeftHeight = $('#dm_custom_bottom').find('.dm_zone.left').outerHeight(true);
		// $.fn.frontFramework.debug("customBottomHeight : " + customBottomHeight + " customBottomLeftHeight : " + customBottomLeftHeight);

		//on ne rajoute de l'espace que si la zone est insuffisamment grande pour le contenir
		if(customBottomHeight != null && customBottomLeftHeight!= null && customBottomHeight < customBottomLeftHeight) {
			//on ne rajoute que l'espace manquant
			//en soustrayant la hauteur de la zone située en bas à gauche au calcul à posteriori
			options.isPostHSL = true;
			options.offsetHSL += (customBottomHeight - customBottomLeftHeight);
		}

		//appel de la fonction de redimenssionnement générale
		$.fn.frontFramework.resizeCols(options);
	}

	//lancement automatique de la fonction
	$(document).ready(function(){
		$('html').frontTemplate();

		//inversion ordre du wrapper dans les listings (pour passer devant l'image quand présente)
		// $('.elements .element .imageWrapper').each(function(index) {
		// 	$(this).insertAfter($(this).parent().children('.wrapper').children('.subWrapper'));
		// });
	});

	//lorsque la fenêtre est redimmensionnée
	$(window).resize(function() {
		//lancement de la fonction de replacement avec un délai lors du redimenssionnement
		$.fn.frontFramework.delay(function(){
			$('html').frontTemplate();
		}, 500);
	});

})(jQuery);