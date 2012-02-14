<?php

class actusDuCabinetActuArticlesContextuelView extends dmWidgetPluginView {

    public function configure() {
        parent::configure();

        $this->addRequiredVar(array(
            'titreBloc',
            'titreLien',
            'nbArticles',
            'longueurTexte',
            'photo',
            'chapo'
        ));
    }
	
	public function getStylesheets() {
		//on créé un nouveau tableau car c'est un nouveau widget (si c'est une extension utiliser $stylesheets = parent::getStylesheets();)
		$stylesheets = array();
		
		//lien vers le js associé au menu
		$cssLink = '/theme/css/_templates/'.dmConfig::get('site_theme').'/Widgets/ActusDuCabinetActuArticlesContextuel/ActusDuCabinetActuArticlesContextuel.css';
		//chargement de la CSS si existante
		if (is_file(sfConfig::get('sf_web_dir') . $cssLink)) $stylesheets[] = $cssLink;
		
		return $stylesheets;
	}

    protected function doRender() {
        $vars = $this->getViewVars();
        $arrayArticle = array();

        $idDmPage = sfContext::getInstance()->getPage()->id;
        $dmPage = dmDb::table('DmPage')->findOneById($idDmPage);
        //$arrayArticle[] = $dmPage->module.' - '.$dmPage->action.' - '.$dmPage->record_id;

        switch ($dmPage->module . '/' . $dmPage->action) {

//            case 'pageCabinet/equipe':
//                break;

            case 'section/show':
                // il faut que je récupère l'id de la rubrique de la section
                // je récupère donc l'ancestor de la page courante pour extraire le record_id de ce dernier afin de retrouver la rubrique
                $ancestors = $this->context->getPage()->getNode()->getAncestors();
                $recordId = $ancestors[count($ancestors) - 1]->getRecordId();
                $actuArticles = Doctrine_Query::create()->from('SidActuArticle a')
                        ->leftJoin('a.SidActuArticleSidRubrique sas')
                        ->leftJoin('sas.SidRubrique s')
                        ->leftJoin('a.SidActuTypeArticle sata')
                        ->where('s.id = ?  ', array($recordId))
                        ->andWhere('a.is_active = ?', true)
                        ->andWhere('sata.sid_actu_type_id = ?', array($vars['type']))
                        ->limit($vars['nbArticles'])
                        ->execute();

                // Si il n'y a pas d'actus associées, on en affiche la dernière actu

                if (count($actuArticles) == 0) {
                    $actuArticles = '';
                    $actuArticles = Doctrine_Query::create()->from('SidActuArticle a')
                            ->leftJoin('a.Translation b')
                            ->leftJoin('a.SidActuTypeArticle sata')
                            ->andWhere('a.is_active = ?', true)
                            ->andWhere('sata.sid_actu_type_id = ?', array($vars['type']))
                            ->orderBy('b.updated_at DESC')
                            ->limit($vars['nbArticles'])
                            ->execute();
                }
                foreach ($actuArticles as $actuArticle) { // on stock les NB actu article 
                    $arrayArticle[$actuArticle->id] = $actuArticle;
                }
                break;
            case 'rubrique/show':
                // toutes les sections de la rubrique contextuelle
                $rubriques = dmDb::table('SidRubrique')->findById($dmPage->record_id);
                // on parcourt les sections pour extraire les articles
                foreach ($rubriques as $rubrique) {
                    $actuArticles = Doctrine_Query::create()->from('SidActuArticle a')
                            ->leftJoin('a.Translation b')
                            ->leftJoin('a.SidActuArticleSidRubrique sas')
                            ->leftJoin('sas.SidRubrique s')
                            ->leftJoin('a.SidActuTypeArticle sata')
                            ->where('s.id = ? ', array($rubrique->id))
                            ->andWhere('a.is_active = ?', true)
                            ->andWhere('sata.sid_actu_type_id = ?', array($vars['type']))
                            ->orderBy('b.updated_at DESC')
                            ->limit($vars['nbArticles'])
                            ->execute();

                    // Si il n'y a pas d'actus associées, on en affiche la dernière actu

                    if (count($actuArticles) == 0) {
                        $actuArticles = '';
                        $actuArticles = Doctrine_Query::create()->from('SidActuArticle a')
                            ->leftJoin('a.Translation b')
                                ->leftJoin('a.SidActuTypeArticle sata')
                                ->andWhere('a.is_active = ?', true)
                                ->andWhere('sata.sid_actu_type_id = ?', array($vars['type']))
                                ->orderBy('b.updated_at DESC')
                                ->limit($vars['nbArticles'])
                                ->execute();
                    }
                    foreach ($actuArticles as $actuArticle) { // on stock les NB actu article 
                        $arrayArticle[$actuArticle->id] = $actuArticle;
                    }
                }
                break;

//            case 'sidActuArticle/show':
                // dans la page d'affichage des actu article on n'affiche pas l'article qui est affiché dans le page.content
//                $actuArticles = Doctrine_Query::create()->from('SidActuArticle a')
//                        ->leftJoin('a.SidActuTypeArticle sata')
//                        ->where('a.is_active = ?', true)
//                        ->andWhere('a.id <> ?', $dmPage->record_id)
//                        ->andWhere('sata.sid_actu_type_id = ?', array($vars['type']))
//                        ->orderBy('a.updated_at DESC')
//                        ->limit($vars['nbArticles'])
//                        ->execute();
//
//                // Si il n'y a pas d'actus associées, on en affiche la dernière actu
//
//                if (count($actuArticles) == 0) {
//                    $actuArticles = '';
//                    $actuArticles = Doctrine_Query::create()->from('SidActuArticle a')
//                            ->leftJoin('a.SidActuTypeArticle sata')
//                            ->andWhere('a.is_active = ?', true)
//                            ->andWhere('sata.sid_actu_type_id = ?', array($vars['type']))
//                            ->orderBy('a.updated_at DESC')
//                            ->limit($vars['nbArticles'])
//                            ->execute();
//                }
//                foreach ($actuArticles as $actuArticle) { // on stock les NB actu article 
//                    $arrayArticle[$actuArticle->id] = $actuArticle;
//                }
//                break;
//            case 'sidActuArticle/list':
//                break;

            default:
                // hors context, on renvoie la dernière actu mise à jour
                $actuArticles = Doctrine_Query::create()->from('SidActuArticle a')
                            ->leftJoin('a.Translation b')
                        ->leftJoin('a.SidActuTypeArticle sata')
                        ->Where('a.is_active = ?', true)
                        ->andWhere('sata.sid_actu_type_id = ?', array($vars['type']))
                        ->orderBy('b.updated_at DESC')
                        ->limit($vars['nbArticles'])
                        ->execute();
                foreach ($actuArticles as $actuArticle) { // on stock les NB actu article 
                    $arrayArticle[$actuArticle->id] = $actuArticle;
                }
        }

        return $this->getHelper()->renderPartial('actusDuCabinet', 'actuArticlesContextuel', array(
                    'articles' => $arrayArticle,
                    'nbArticles' => $vars['nbArticles'],
                    'titreBloc' => $vars['titreBloc'],
                    'titreLien' => $vars['titreLien'],
                    'longueurTexte' => $vars['longueurTexte'],
                    'photo' => $vars['photo'],
                    'chapo' => $vars['chapo'],
                ));
    }

}
