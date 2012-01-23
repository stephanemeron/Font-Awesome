<?php
use_helper('Date');
// var partial : $article, $titreBloc
        
echo '<article itemscope itemtype="http://schema.org/Article">';
if ($titreBloc == true) {
            echo _tag('h2.title itemprop="name"', $articles->getTitle());
        }

	    $imgLink = '/uploads/' . $articles->getImage();
//on vérifie que l'image existe
    $imgExist = is_file(sfConfig::get('sf_web_dir') . $imgLink);

	// on teste si le fichier image est présent sur le serveur avec son chemin absolu
	if ($imgExist) {
		echo _open('div.imageFullWrapper');
			echo _media($imgLink)
						->set('.image itemprop="image"')
						->alt($articles->getTitle())
						//redimenssionnement propre lorsque l'image sera en bibliothèque
						->width(spLessCss::gridGetContentWidth());
						//->height(spLessCss::gridGetHeight(14,0))
		echo _close('div');
	}
	
	echo _tag('p.teaser', $articles->getResume());
        echo _tag('section.contentBody itemprop="articleBody"', $articles->getText());
        echo _tag('p.meta', get_partial('global/dateWrapperFull', array('node'	=>	$articles)));
		
//Fermeture de l'article
echo _close('article');
?>