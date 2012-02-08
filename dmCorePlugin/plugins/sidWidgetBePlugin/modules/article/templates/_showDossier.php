<?php
/**
 * Retourne un article de dossier xml formaté par le XSL, en html
 */

//html de sortie
$html = '';

//récupération de la section et de la rubrique
$section = $article->getSectionPageTitle();
$rubrique = $article->getRubriquePageTitle();

//ciblage XML et XSL
$xml = sfConfig::get('app_rep-local') .
        $article->getSection()->getRubrique() .
        '/' .
        $article->getSection() .
        '/' .
        $article->filename .
        '.xml';
$xsl = dm::getDir() . '/dmCorePlugin/plugins/sidWidgetBePlugin/lib/xsl/' . sfConfig::get('app_xsl-article');

// vérification du fichier XSL
if (!is_file($xsl)) $html.= debugTools::infoDebug(array(__('Error : missed file') => $xsl),'warning');

// vérification du fichier XML
if (!is_file($xml)) {
	$html.= debugTools::infoDebug(array(__('Error : missed file') => $xml),'warning');
} else {
	//récupération des différentes variables par défault
	$dash = _tag('span.dash', sfConfig::get('app_vars-partial_dash'));
	
	//titre du contenu
	$html.= get_partial('global/titleWidget', array('title' => $rubrique . $dash . $section, 'isContainer' => true));

	//création du parser XML
	$doc_xml = new DOMDocument();

	//ouverture du document XML
	if ($doc_xml->load($xml)) {

		//récupération du contenu du XML
		$doc_xsl = new DOMDocument();
		$doc_xsl->load($xsl);
		$moteurXslt = new xsltProcessor();
		$moteurXslt->importstylesheet($doc_xsl);

		//création du contenu à afficher
		$articleBody = $moteurXslt->transformToXML($doc_xml);


		//récupération des articles associées au dossier affiché
		$sections = $doc_xml->getElementsByTagName("Section");
		$linkedArticles = array();

		//remplissage d'un tableau de valeur contenant les id des articles associés
		foreach ($sections as $section) {
			$AssociatedWiths = $section->getElementsByTagName("AssociatedWith");
			foreach ($AssociatedWiths as $AssociatedWith) {
				if(isset($AssociatedWith->getElementsByTagName("Reference")->item(0)->nodeValue)) $linkedArticles[] = $AssociatedWith->getElementsByTagName("Reference")->item(0)->nodeValue;
			}
		}

		$html.= "linkedArticles : " . print_r($linkedArticles, true);

		//compteur
		$count = 0;
		$maxCount = count($linkedArticles);

		//récupération des articles et ajout à l'intérieur du contenu
		foreach ($linkedArticles as $linkedArticle) {
			//incrémentation compteur
			$count++;

			//récupérarion du nom de fichier de l'article en question directement dans la base
			$linkedSidArticle = Doctrine_Core::getTable('SidArticle')->findOneByFilenameAndSectionId($linkedArticle, $article->sectionId);
			//ajout information de débug
			//$articleBody.= debugTools::infoDebug(array('ID LEA' => $linkedArticle . ' - ' . $article->sectionId));

			$articleBody.= get_partial('article/showArticleInDossier', array('article' => $linkedSidArticle, 'count' => $count, 'maxCount' => $maxCount));
		}

		//affichage du contenu
		$articleOpts = array(
							'container' => 'article',
							'name' => $article->title,
							'description' => $article->getChapeau(),
							'image' => '/_images/lea' . $article->filename . '-g.jpg',
							'dateCreated' => $article->created_at,
							'dateModified' => $article->updated_at,
							'articleBody' => $articleBody,
							'copyrightHolder' => 'SID Presse',							//en attendant implémentation dans base depuis la valeur du XML
							'copyrightYear' => substr($article->created_at, 0, 4)		//en attendant implémentation dans base depuis la valeur du XML
						);

		$html.= get_partial('global/schema/Thing/CreativeWork/Article', $articleOpts);





		/*

		echo $return; // on affiche les titres et chapeau de l'article principal du dossier avant d'afficher tous les sous articles puis de fermer la balise article
			//  affichage brut des articles
			foreach ($linkedArticles as $linkedArticle) {
				$linkedSidArticle = Doctrine_Core::getTable('SidArticle')->findOneByFilenameAndSectionId($linkedArticle, $article->sectionId);
				echo debugTools::infoDebug(array('ID LEA' => $linkedArticle.' - '.$article->sectionId));
				// affichage du texte de l'article avec le xsl
				include_partial('article/showArticleInDossier', array('article' => $linkedSidArticle));
			}
		*/	
	} else {
		$html.= debugTools::infoDebug(array(__('Error : invalid xml') => $xml),'warning');
	}
}

//affichage html en sortie
echo $html;