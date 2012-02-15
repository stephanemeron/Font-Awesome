<?php

/**
 * Mon bandeau components
 * 
 * No redirection nor database manipulation ( insert, update, delete ) here
 * 
 * 
 */
class bandeauComponents extends myFrontModuleComponents {

    public function executeShow() {
        //insertion de la CSS du widget du theme courant
        $this->getResponse()->addStylesheet('/theme/css/_templates/'.dmConfig::get('site_theme'). '/Widgets/BandeauSmartBandeau/BandeauSmartBandeau.css');

        //    $this->groupe = $this->getRequest()->getParameter('id');
        $query = $this->getShowQuery();
        $this->gererMonBandeau = $this->getRecord($query);
        //        $this->gererMonBandeau = dmDb::table('sid_bandeau')->findOneById(1);
        // initialisation du tableau et de la variable
        $arrayMarquees = array();
        $arrayMessage['message'] = array();
        $attribut = '';
        //echo $this->gererMonBandeau->getDebutDate().'---'.$this->gererMonBandeau->getFinDate();
        // je teste si le message du bandeau est active ou non
        if ($this->gererMonBandeau->getIsActive() == TRUE) {
            // je teste si la date du jour est comprise entre les dates de début et de fin
            if (
                    ($this->gererMonBandeau->getDebutDate() <= date('Y-m-d') && $this->gererMonBandeau->getFinDate() >= date('Y-m-d'))  // date du jour comprise dans l'intervale debut / fin
                    || ($this->gererMonBandeau->getDebutDate() <= date('Y-m-d') && $this->gererMonBandeau->getFinDate() == '') // date du jour postérieure à la date de début et date de fin vide
                    || ($this->gererMonBandeau->getDebutDate() == '' && $this->gererMonBandeau->getFinDate() >= date('Y-m-d')) // date du jour antérieure à la date de fin et date de début vide                           
                    || ($this->gererMonBandeau->getDebutDate() == '' && $this->gererMonBandeau->getFinDate() == '')  // dates debut et fin non renseignées
            ) {
                if ($this->gererMonBandeau->getScrollamount() != 0)
                    $arrayMarquees['scrollamount'] = 'scrollamount="' . $this->gererMonBandeau->getScrollamount() . '"';
                if ($this->gererMonBandeau->getScrolldelay() != 0)
                    $arrayMarquees['scrolldelay'] = 'scrolldelay="' . $this->gererMonBandeau->getScrolldelay() . '"';
                //if ($this->gererMonBandeau->getBoucle() != 0)
                $arrayMarquees['loop'] = 'loop="' . $this->gererMonBandeau->getBoucle() . '"';

                // taille de l'anuimation
                if ($this->gererMonBandeau->getWidth() != 0)
                    $arrayMarquees['width'] = 'width="' . $this->gererMonBandeau->getWidth() . '"';
                if ($this->gererMonBandeau->getHeight() != 0)
                    $arrayMarquees['height'] = 'height="' . $this->gererMonBandeau->getHeight() . '"';

                $arrayMarquees['behavior'] = 'behavior="' . $this->gererMonBandeau->getBehavior() . '"';
                $arrayMarquees['direction'] = 'direction="' . $this->gererMonBandeau->getDirection() . '"';
                // mise en tableau des données pour les mouvement du bandeau
                foreach ($arrayMarquees as $arrayMarquee) {
                    $attribut .= $arrayMarquee . ' ';
                }
                // mise en tableau du message
                //$arrayMessage['message'] = $this->gererMonBandeau->getTitle(); // pas besoin
            } else {
                // si la date ci dessus n'est plus valide, on lance le message par défaut
                //                $attribut = 'behavior="scroll" direction="right"';
                //                $arrayMessage['message'] = 'Message par défaut';
                // on désactive plutot ;)
                $this->gererMonBandeau->isActive = false;
                $this->gererMonBandeau->save();
            }

            $this->attribut = $attribut;
            //$this->gererMonBandeau = $arrayMessage['message'];
        }
    }

    public function executeSmartBandeau() {
        $bandeaux = dmDb::table('SidBandeau')->findByIsActive(true);
        foreach ($bandeaux as $bandeau) {
            
            if ($this->getPage()->id == $bandeau->GroupeBandeau->dm_page_id) {
                $this->bandeauId = $bandeau->id;
                break;
            } else {
                $ancestors = $this->context->getPage()->getNode()->getAncestors();
                    $i = count($ancestors)-1;
                    foreach ($ancestors as $i => $ancestor) {
                        if ($ancestor->id == $bandeau->GroupeBandeau->dm_page_id) {
                            $this->bandeauId = $bandeau->id;
                            break;
                        }
                        else
                            $i--;
                    }
            }
        }
    }

}
