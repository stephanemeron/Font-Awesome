// dmWidgetSearchForm.js
// v0.1
// Last Updated : 2012-03-19 17:25
// Copyright : SID Presse
// Author : Arnaud GAUDIN

//permet de gérer plus facilement l'affichage du widget de recherche
(function($) {

	$.fn.dmWidgetSearchForm = function() {
		//test ajout de debug
		$.fn.dmWidgetSearchForm.debug("dmWidgetSearchForm : initialisation");

		// iterate and reformat each matched element
		return this.each(function() {
			var form = $(this).find('form');
			
			//on sélectionne le champ de saisie de la recherche
			form.find('input.query').bind('focusin', function() {
				//ajout de la classe seulement si absente
				if(!form.hasClass('focus')) form.addClass('focus');
			}).bind('focusout', function() {
				//suppression de la classe seulement si présente
				if(form.hasClass('focus')) form.removeClass('focus');
			});
		});
	}

	//fonction de debuggage
	$.fn.dmWidgetSearchForm.debug = function(txt){
		if (window.console && window.console.log)
			window.console.log(txt);
	}

	//lancement lorsque le document est chargé
	$(document).ready(function(){
		$('.dm_widget.search_form').dmWidgetSearchForm();
	});

})(jQuery);