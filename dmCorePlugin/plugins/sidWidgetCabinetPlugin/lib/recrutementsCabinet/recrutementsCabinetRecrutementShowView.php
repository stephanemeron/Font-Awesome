<?php

class recrutementsCabinetRecrutementShowView extends dmWidgetPluginView {

    public function configure() {
        parent::configure();

        $this->addRequiredVar(array(
            'titreBloc',
            'withImage',
            'widthImage',
            'heightImage',
        ));
    }

    /**
     * On affiche NB articles Actu selon 3 types:
     * 1) il est sur une dmPage automatique de type "Rubrique" : on affiche les articles qui sont dans les sections de cette rubrique
     * 2) il est sur une dmPage automatique de type "Section" : on affiche les articles qui sont dans cette section
     * 3) il est sur une page ni Rubrique ni Section, automatique ou pas : on affiche les articles de la rubrique choisie par défaut
     *  
     */
    protected function doRender() {
        $vars = $this->getViewVars();
        $dmPage = sfContext::getInstance()->getPage();
       
        if($vars['titreBloc'] == NULL || $vars['titreBloc'] == " "){
        $vars['titreBloc'] = $dmPage->getName();
        };
        $recrutements = dmDb::table('SidCabinetRecrutement')->findOneByIdAndIsActive($dmPage->record_id,true);
        return $this->getHelper()->renderPartial('recrutementsCabinet', 'recrutementShow', array(
                    'recrutements' => $recrutements,
                    'titreBloc' => $vars['titreBloc'],
                    'width' => $vars['widthImage'],
                    'withImage' => $vars['withImage'],
            
                ));
    }

}
