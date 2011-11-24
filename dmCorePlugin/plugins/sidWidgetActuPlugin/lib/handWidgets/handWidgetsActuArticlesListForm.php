<?php

class handWidgetsActuArticlesListForm extends dmWidgetPluginForm {

    public function configure() {
        $this->widgetSchema['type'] = new sfWidgetFormDoctrineChoice(array(
                    'model' => 'SidActuType'
                ));
        $this->validatorSchema['type'] = new sfValidatorDoctrineChoice(array(
                    'required' => true,
                    'model' => 'SidActuType'
                ));

        $this->widgetSchema['titreBloc'] = new sfWidgetFormInputText();
        $this->validatorSchema['titreBloc'] = new sfValidatorString(array(
                    'required' => false
                ));

        $this->widgetSchema['nbArticles'] = new sfWidgetFormInputText(array('default' => 0));
        $this->validatorSchema['nbArticles'] = new sfValidatorInteger(array(
                    'required' => true
                ));
        
        $this->widgetSchema['longueurTexte'] = new sfWidgetFormInputText(array('default' => 0));
        $this->validatorSchema['longueurTexte'] = new sfValidatorInteger(array(
                    'required' => false
                ));
        
        $this->widgetSchema['photo'] = new sfWidgetFormInputCheckbox(array('default'=> true));
        $this->validatorSchema['photo']  = new sfValidatorBoolean();
        
        $this->widgetSchema['chapo'] = new sfWidgetFormSelectRadio(array('choices' => array('chapeau','texte'),'default'=>1));
        $this->validatorSchema['chapo']  = new sfValidatorChoice(array('choices' =>array(0,1),'multiple' => false));
        
        $this->widgetSchema->setHelps(array(
            'type' => 'Le type de l\'article',
            'titreBloc' => 'Le titre optionnel du bloc.',  
            'nbArticles' => 'Le nombre maximum d\'articles affichés.',            
            'longueurTexte' => 'Longueur du texte avant de la tronquer',
            'photo' => 'affiche ou pas la photo',
            'chapo' => 'Choisir si on veut afficher le résumé de la page ou le contenu entier de la page'
        ));

        parent::configure();
    }

    public function getStylesheets() {
        return array(
            'lib.ui-tabs'
        );
    }

    public function getJavascripts() {
        return array(
            'lib.ui-tabs',
            'core.tabForm',
            'sidWidgetBePlugin.widgetShowForm'
        );
    }

    protected function renderContent($attributes) {
        return $this->getHelper()->renderPartial('handWidgets', 'actuArticlesListForm', array(
            'form' => $this,
            'id' => 'sid_widget_actu_articles_contextuel_' . $this->dmWidget->get('id')
        ));
    }

    public function getWidgetValues() {
        $values = parent::getWidgetValues();

        return $values;
    }

}