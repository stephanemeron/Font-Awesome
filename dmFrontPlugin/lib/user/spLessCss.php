<?php

class spLessCss extends dmFrontUser {
	
	//fonction d'inialisation de la page (permet de centraliser les fonctionns appelées)
	public static function pageInit($optionsCustom = array()) {
		//Récupération des options de la page (avec fusion des options personnalisées)
		$pageOptions = self::pageTemplateGetOptions($optionsCustom);
		
		//action à effectuer uniquement en DEV
		if ($pageOptions['idDev']) {
			
			//génération des sprites
			//self::spriteInit();
		
			//affichage du widget de DEBUG du framework
			echo dm_get_widget('sidSPLessCss', 'debug', array());
		}
		
		return $pageOptions;
	}
		
	//génération de toutes les sprites dans toutes les dimensions
	public static function spriteInit($spriteFormat = '', $spriteListing = array (), $lessDefinitions = '') {
		
		//génération des Sprites à différentes résolutions
		/*
		$output_S = self::spriteGenerate($spriteListing, 'S');
		$output_M = self::spriteGenerate($spriteListing, 'M');
		$output_L = self::spriteGenerate($spriteListing, 'L');
		$output_X = self::spriteGenerate($spriteListing, 'X');
		
		//composition des définitions de fichiers
		$lessDefinitions = $output_S . PHP_EOL;
		$lessDefinitions.= $output_M . PHP_EOL;
		$lessDefinitions.= $output_L . PHP_EOL;
		$lessDefinitions.= $output_X . PHP_EOL;
		*/
		
		//Si on est au début de l'action
		if($spriteFormat == '') {
			//récupération du listing des sprites
			$spriteListing = self::spriteGetListing();
			
			//purge des miniatures
			self::spriteReset($spriteListing);
		}
		
		//on définit le pourcentage d'avancement en fonction de la miniature effectuée
		//on change également la valeur de spriteFormat pour la boucle suivante
		switch ($spriteFormat) {
			case '':
				$spriteFormat = 'S';
				$prct = 25;
				break;
			case 'S':
				$spriteFormat = 'M';
				$prct = 50;
				break;
			case 'M':
				$spriteFormat = 'L';
				$prct = 75;
				break;
			case 'L':
				$spriteFormat = 'X';
				$prct = 100;
				break;
			case 'X':
				//on quite la fonction pour éviter une boucle infinie
				return false;
				break;
			default:
				$prct = 0;
				break;
		}
		
		//génération des sprites à la résolution sélectionnée
		$lessDefinitions.= self::spriteGenerate($spriteListing, $spriteFormat) . PHP_EOL;
		
		//Si on est à la fin de l'action, génération du fichier less de sortie
		if($prct == 100) self::spriteLessGenerate($lessDefinitions);
		
		//Renvoi de valeurs pour l'affichage
		return array(
			'spriteFormat'		=> $spriteFormat,
			'spriteListing'		=> $spriteListing,
			'lessDefinitions'	=> $lessDefinitions,
			'prct'				=> $prct
		);
	}
	
	//génération du listing des icônes
	private static function spriteGetListing() {
		//emplacement et récupération des thèmes de sprites
		$urlThemes = sfConfig::get('sf_web_dir') . sfConfig::get('sf_img_path_framework') . '/Sprites';
		$urlThemesClient = sfConfig::get('sf_web_dir') . sfConfig::get('sf_img_path_client') . '/Sprites';
		$getThemes = sfFinder::type('directory')->follow_link()->relative()->in($urlThemes);
		
		//création du tableau de stockage des sprites
		$spriteListing = array();
		
		//on parcourt tous les thèmes repérés
		foreach ($getThemes as $theme) {
			//emplacement et récupération des sprites du thème
			$urlSprites = $urlThemes . '/' . $theme;
			$getSprites = sfFinder::type('file')->name('*.svg')->follow_link()->relative()->in($urlSprites);
			
			
			//emplacement et récupération des sprites assemblées déjà générées
			$urlSpritesClient = $urlThemesClient . '/' . $theme;
			$spriteListing[$theme]['output'] =  sfFinder::type('file')->name($theme . '-*.png')->follow_link()->in($urlThemesClient);
			
			//on parcourt les sprites trouvées dans le theme
			foreach ($getSprites as $sprite) {
				//décomposition du nom categorie-icone.svg
				$decomp = explode('-', substr($sprite, 0, -4));
				
				//on part du modèle suivant :
				//categorie-icone.svg
				$spriteListing[$theme]['categories'][$decomp[0]][$decomp[1]]['urlFramework'] = $urlSprites . '/' . $sprite;
				$spriteListing[$theme]['categories'][$decomp[0]][$decomp[1]]['urlClient'] = $urlSpritesClient . '/' . $sprite;
			}
		}
		
		return $spriteListing;
	}
	
	//purge des miniatures
	private static function spriteReset($spriteListing = array()){
		//on parcourt les themes
		foreach ($spriteListing as $theme => $info) {
			//récupération des miniatures assemblées déjà générées et des catégories
			$outputs = $info['output'];
			$categories = $info['categories'];
			
			//suppression des assemblages déjà générés
			foreach ($outputs as $output) {
				if(is_file($output)) {
					$testUnlink = unlink($output);
					//affichage d'un message en cas d'erreur
					if(!$testUnlink) die("spLessCss | spriteInit : erreur de suppression du fichier d'assemblage : " . $output);
				}
			}
			
			//suppression des SVG déjà générés
			foreach ($categories as $category => $sprites) {
				foreach ($sprites as $sprite => $value) {
					if(is_file($value['urlClient'])) {
						$testUnlink = unlink($value['urlClient']);
						//affichage d'un message en cas d'erreur
						if(!$testUnlink) die("spLessCss | spriteInit : erreur de suppression du fichier SVG : " . $value['urlClient']);
					}
				}
			}
			
			//suppression des dossiers de thème
			$urlThemeClient = sfConfig::get('sf_web_dir') . sfConfig::get('sf_img_path_client') . '/Sprites/' . $theme;
			if(is_dir($urlThemeClient)){
				$testRmdir = rmdir($urlThemeClient);
				//affichage d'un message en cas d'erreur
				if(!$testRmdir) die("spLessCss | spriteGenerate : erreur de suppression du dossier : " . $urlThemeClient);
			}
		}
		
	}
	
	//génération des appels de la fonctions less de génération des sprites
	private static function spriteLessGenerate($lessDefinitions = "") {
		//chemin vers le fichier de config des sprites
		$urlSpriteGenerate = sfConfig::get('sf_web_dir') . sfConfig::get('sf_img_path_framework') . '/Sprites/_SpriteGenerate.less';
		
		//création du système de fichier
		$fs = new sfFilesystem();
		
		//suppression préventive du fichier
		if(is_file($urlSpriteGenerate)) $fs->remove($urlSpriteGenerate);
		
		//création du fichier
		$fs->touch($urlSpriteGenerate);
		
		//ajout des données de copyright dans le fichier
		$headerInfo = "// _SpriteGenerate.less" . PHP_EOL;
		$headerInfo.= "// v1.0" . PHP_EOL;
		$headerInfo.= "// Last Updated : " . date('Y-m-d H:i') . PHP_EOL;
		$headerInfo.= "// Copyright : SID Presse" . PHP_EOL;
		$headerInfo.= "// Author : Arnaud GAUDIN" . PHP_EOL;
		
		//composition des données contenues dans le fichier
		$fileContent = $headerInfo . PHP_EOL . $lessDefinitions;
		
		//écriture du fichier
		file_put_contents($urlSpriteGenerate, $fileContent);
	}
	
	//génération d'un appel LESS de la fonction de génération de sprite
	private static function spriteLessDefinition($theme = "Default", $category, $name, $spriteFormat = "L", $offX = 0, $offY = 0) {
		
		//composition du nom de la classe
		$output = '.sprite';
		if($theme != "Default") $output.= '-' . $theme;
		$output.= '-' . $category;
		$output.= '-' . $name;
		$output.= '-' . $spriteFormat;
		
		//ouverture fonction LESS
		$output.= ' { @spriteDefinition(';
		
		//ajout paramètres
		$output.= '"' . $theme . '"';
		$output.= '; "' . $category . '"';
		$output.= '; "' . $name . '"';
		$output.= '; "' . $spriteFormat . '"';
		$output.= '; @spriteFormat_' . $spriteFormat;
		$output.= '; ' . $offX;
		$output.= '; ' . $offY;
		
		//fermeture fonction LESS
		$output.= '); }';
		
		//Ajout retour ligne
		$output.= PHP_EOL;
		
		return $output;
	}
	
	//copie de toutes les icônes et changement des couleurs
	private static function spriteGenerate($spriteListing = array(), $spriteFormat = 'L'){
		//dimension par défaut des sprites (imposé par le format SVG utilisé)
		$dimDefault = intval(self::getLessParam('spriteFormat'));
		//dimension des sprites dans les paramètres du framework (égale à S, M, L ou X)
		$dimFramework = intval(self::getLessParam('spriteFormat_' . $spriteFormat));
		
		//calcul du ratio de redimenssionnement
		$resizeRatio = $dimFramework / $dimDefault;
		
		//calcul de la densité (par défaut résolution de 72dpi)
		$density = 72 * $resizeRatio;
		$execDensity = ($resizeRatio != 1) ? ' -density ' . $density : null;
		
		//génération du code LESS en sortie
		$output = "";
		
		//on parcourt les themes
		foreach ($spriteListing as $theme => $info) {
			//récupération des catégories
			$categories = $info['categories'];
			
			//création du dossier du thème si non présent
			$urlThemeClient = sfConfig::get('sf_web_dir') . sfConfig::get('sf_img_path_client') . '/Sprites/' . $theme;
			if(!is_dir($urlThemeClient)){
				$testMkdir = mkdir($urlThemeClient, 0775, true);
				//affichage d'un message en cas d'erreur
				if(!$testMkdir) die("spLessCss | spriteGenerate : erreur de création de l'arborescence de dossiers");
			}
			
			//Requête : préparations des composantes assemblées par la suite
			$execConvert = "convert";
			$execConvertOptions = " -background none";
			$execConvertDensityGeneral = true;
			$execConvertAppend = "";
			
			//variable de placement et de comptage
			$numCat = 0;
			
			//on parcourt les catégories du theme
			foreach ($categories as $category => $sprites) {
				$numSprite = 0;
				
				//Requête : ouverture parenthèse
				$execConvertAppend.= " \(";
				
				//on parcourt les sprites de la catégorie
				foreach ($sprites as $sprite => $value) {
					//composition des commandes à exécuter
					$execCopy = "cp ". $value['urlFramework'] . " " . $value['urlClient'];
					
					//exécution de la commande de copie
					exec($execCopy);
					
					//on récupère toutes les occurences de la couleur spriteCouleur dans le fichier
					$isMatchColors = preg_match_all('/\#\#spriteCouleur\d[A-Za-z]*\#\#/', file_get_contents($value['urlClient']), $matchColors);
					
					//on parcourt les couleurs trouvées et on les remplace
					foreach ($matchColors[0] as $colorToken) {
						//on enlève les délimiteurs ## de la couleur
						$colorKey = substr($colorToken, 2, -2);
						//on récupère la valeur de la couleur correspondante
						$colorValue = self::getLessParam($colorKey);
						
						//composition de la commmande de changement de couleurs
						$execColor = "perl -pi -w -e 's/" . $colorToken . "/". $colorValue ."/g;' " . $value['urlClient'];
						
						//exécution de la commande
						exec($execColor);
					}
					
					//on récupère les dimensions de l'image dans un tableau
					$spriteInfo = explode('-', exec('identify -format "%w-%h" ' . $value['urlClient']));
					$spriteWidth = $spriteInfo[0];
					$spriteHeight = $spriteInfo[1];
					
					//on vérifie si l'une des dimensions ne correspond pas à la dimenssion de base définie, puis on remultiplie par le ratio général
					$spriteResizeRatio = $dimDefault / max($spriteWidth, $spriteHeight);
					$spriteDensity = 72 * $spriteResizeRatio * $resizeRatio;
					if($spriteResizeRatio != 1) {
						$execSpriteDensity = ' -density ' . $spriteDensity;
						$execConvertAppend.= $execSpriteDensity;
						$execConvertDensityGeneral = false;
					}
					
					//Requête : ajout nom de fichier
					$execConvertAppend.= " " . $value['urlClient'];
					
					//ajout de l'appel LESS à la variable de sortie
					$output.= self::spriteLessDefinition($theme, $category, $sprite, $spriteFormat, $numSprite, $numCat);
					
					//incrémentation numéro de sprite
					$numSprite++;
				}
				
				//Requête : append horizontal et fermeture parenthèse
				$execConvertAppend.= " +append \)";
				
				//incrémentation numéro de catégorie
				$numCat++;
			}
			
			//Requête : assemblage final
			if($execConvertDensityGeneral) $execConvertOptions.= $execDensity;
			$execConvert.= $execConvertOptions . $execConvertAppend;
			$execConvert.= " -append " . $urlThemeClient . "-" . $spriteFormat . ".png";
			
			//Requête : exécution
			exec($execConvert);
		}
		
		//retour de la valeur de sortie LESS
		return $output;	
	}
	
	//récupération du layout par défaut du template sélectionné
	public static function pageSuccessTemplateInclude() {
		//Ciblage du layout de page par défaut du template sélectionné
		$pageSuccessTemplateInclude = sfConfig::get('dm_core_dir') . '/../themesFmk/_templates/' . self::getLessParam('mainTemplate') . '/Externals/php/layouts/pageSuccessTemplate.php';
		
		//on retourne un tableau contenant 3 clefs
		$includeInfo = array(
							'isFile'	=>	is_file($pageSuccessTemplateInclude),
							'errorMsg'	=>	'Le fichier "' . $pageSuccessTemplateInclude . '" est introuvable',
							'include'	=>	$pageSuccessTemplateInclude
						);
		
		return $includeInfo;
	}
	
	//options de page par défaut
	public static function pageTemplateGetOptions($optionsCustom = array()) {
		
		//récupération du gabarit de la page
		$currentGabarit = sfContext::getInstance()->getPage()->get('gabarit');
		if ($currentGabarit == 'default' || $currentGabarit == '') {
			$currentGabarit = self::getLessParam('templateGabarit');
		}
		
		//composition des options de page par défault
		$pageTemplateOptionsDefault = array(
							'idDev'				=> ((sfConfig::get('sf_environment') == 'dev') ? true : false),
							'currentGabarit'	=> $currentGabarit,
							'areas'				=> array(
								'dm_page_content'		=>	array(
														'areaName'	=> 'content',
														'isActive'	=> true,
														'isPage'	=> true,
														'clearfix'	=> false
													),
								'dm_sidebar_left'	=>	array(
														'areaName'	=> 'left',
														'isActive'	=> (($currentGabarit == 'two-sidebars' || $currentGabarit == 'sidebar-left') ? true : false),
														'isPage'	=> false,
														'clearfix'	=> false
													),
								'dm_sidebar_right'	=>	array(
														'areaName'	=> 'right',
														'isActive'	=> (($currentGabarit == 'two-sidebars' || $currentGabarit == 'sidebar-right') ? true : false),
														'isPage'	=> false,
														'clearfix'	=> false
													)
												)
							);
		
		//on fusionne des éventuelles propriétés personnalisées injectées dans la fonction
		$pageOptions = (count($optionsCustom) === 0) ? $pageTemplateOptionsDefault : self::pageTemplateCustomOptions($pageTemplateOptionsDefault, $optionsCustom);
		
		//retour de la valeur
		return $pageOptions;
	}
	
	//fonction d'insertion de nouvelle Area dans le pageTemplateSuccess
	private static function pageTemplateCustomOptions($options = array(), $customOptions = array()) {
		//on parcourt toutes les zones à insérer
		foreach ($customOptions['areas'] as $id => $area) {
			//on vérifie si un index est définit pour la zone, sinon on la rajoute à la fin
			if(isset($area['index'])) {
				//récupération de l'index d'insertion
				$insertIndex = $area['index'];
				
				//extraction des portions de Areas se trouvant avant et après l'index
				$firstPart = array_slice($options['areas'], 0, $insertIndex, true);
				$lastPart = array_slice($options['areas'], $insertIndex, (count($options['areas']) - $insertIndex), true);
				
				//assemblage dans un tableau temporaire de la zone à rajouter
				$rajout[$id] = $area;
				
				//assemblage du tout
				$options['areas'] = array_merge($firstPart,$rajout,$lastPart);
				
			}
		}
		//une fois que l'on a vérifié toutes les zones à insérer on fusionne les modifications de zones éventuellement présentes
		$options = array_replace_recursive($options, $customOptions);
		
		return $options;
	}

	//calcul de la valeur finale
	public static function lessCalculator($str) {
		$fn = create_function("", "return ({$str});" );
		return $fn();
	}

	//fonction bugguée à terminer
	private static function lessIsNumeric($variableValue) {
		$patternHex = '/^#+(([a-fA-F0-9]){3}){1,2}$/';
		$detectisHex = preg_match_all($patternHex, $variableValue, $matchesNumeric);

		$patternDate = '/([0-9]){4}-([0-9]){2}-([0-9]){2}T([0-9]){2}:([0-9]){2}/';
		$detectisDate = preg_match_all($patternDate, $variableValue, $matchesNumeric);

		//valeur de retour initiale
		$returnValue = 0;

		if($detectisDate > 0) {
			$returnValue = 0;
		}elseif($detectisHex > 0) {
			$returnValue = 0;
		}else{
			$patternNumeric = '/[0-9]+[\(\)\-\+\*\.]*/';
			$detectisNumeric = preg_match_all($patternNumeric, $variableValue, $matchesNumeric);

			if($detectisNumeric > 0){
				$returnValue = 1;
			}
		}
		
		return $returnValue;
	}

	//suppression des unités d'une valeur less
	private static function parseLessValue($variableValue) {
		$units=array(
			'em', 'ex', 'px', 'gd', 'rem', 'vw', 'vh', 'vm', 'ch', // Relative length units
			'in', 'cm', 'mm', 'pt', 'pc', // Absolute length units
			'%', // Percentages
			'deg', 'grad', 'rad', 'turn', // Angles
			'ms', 's', // Times
			'Hz', 'kHz', //Frequencies
		);
		
		foreach ($units as $unit) {
			//on évalue l'expression à la recherche de variables existantes
			$pattern = '/[0-9]+'.$unit.'$/';

			$detectUnits = preg_match_all($pattern, $variableValue, $matches, PREG_OFFSET_CAPTURE);

			foreach ($matches[0] as $unitValue) {
				$value = $unitValue[0];
				$lengthValue = strlen($unit) * -1;
				$finalValue = substr($value, 0, $lengthValue);
				$variableValue = str_replace($unitValue[0], $finalValue, $variableValue);
			}
		}

		//on dégage les pourcentages de façon manuelle
		$variableValue = str_replace('%', '', $variableValue);

		$detectisNumeric = self::lessIsNumeric($variableValue);
		if($detectisNumeric>0){
			$variableValue = self::lessCalculator($variableValue);
		}else{
			//À améliorer éventuellement : suppression des crochets dans les string contenant des variables
			$variableValue = str_replace('{', '', $variableValue);
			$variableValue = str_replace('}', '', $variableValue);
		}
		
		return $variableValue;
	}

	//évaluation de la valeur d'une variable less
	private static function parseLessVariable($variableValue, $parameterValue = array()) {
		//on évalue l'expression à la recherche de variables existantes
		$patternParam = '/@[A-Za-z0-9_]*/';

		$detectSubVariable = preg_match_all($patternParam, $variableValue, $matches, PREG_OFFSET_CAPTURE);
		
		if ($detectSubVariable > 0) {

			$matchesSorted = array();
			$matchesLengths = array();
			$replace = array();

			//remplissage du tableau de valeurs de match
			foreach ($matches[0] as $value) {
				$matchesSorted[] = $value[0];
			}
			
			//triage du tableau de match en fonction de leurs longueurs
			foreach($matchesSorted as $key => $value){
				$matchesLengths[$key]  = strlen($value);
			}
			array_multisort($matchesLengths, SORT_DESC, SORT_NUMERIC, $matchesSorted);
			//print_r($matchesSorted);

			//remplacement des valeurs
			foreach ($matchesSorted as $value) {
				$replace = $parameterValue['variable'][substr($value,1)];

				$variableValue = str_replace($value, $replace, $variableValue);
				
				//on vérifie si la valeur contient à nouveau des valeurs à remplacer
				$detectSubVariableRecursive = preg_match_all($patternParam, $variableValue, $matchesRecursive, PREG_OFFSET_CAPTURE);
				
				if($detectSubVariableRecursive > 0){
					//on relance la fonction de façon récursive
					$variableValue = self::parseLessVariable($variableValue, $parameterValue);
				}
			}
		}
		//on supprime toutes les unités
		$variableValue = self::parseLessValue($variableValue);

		return $variableValue;
	}

    //AJOUT DE FONCTION PERSONNALISEES ICI DANS LE DOUTE (oÃ¹ les mettre dans le modÃ¨le MVC ?)
    public static function loadLessParameters($options = array(), $parameterValue = array()) {
        //on détecte que le paramÃ¨tre lessFile est présent sinon on quitte la fonction
        if ($options['lessFile'] == '') {
            //$options['type'] == '' || 
            return false;
        }

        //Parser de la config du theme choisi
        //ouverture du fichier less à lire
        $lessParserOpen = fopen($options['lessFile'], "r");

        //détection erreur d'ouverture
        if ($lessParserOpen === false) {
            return false;
        } else {
            //initialisation du compteur de ligne
            $lessCounter = 0;

            //lecture ligne par ligne du contenu du fichier
            while (!feof($lessParserOpen)) {
                //incrémentation du compteur de ligne
                $lessCounter++;

                //contenu ligne courante
                $lessCurrentLine = fgets($lessParserOpen, 4096);

                //déclaration des variables
                $lessParserParamName = '';
                $lessParserParamValue = '';

                //on détecte de quel type d'import il s'agit (variable ou import)
                $patternDetectVariable = '/^@[A-Za-z0-9_ ]*:/';
                $patternDetectImport = '/^@import /';

                //on vérifie s'il s'agit d'une ligne de variable ou d'import
                $detectVariable = preg_match($patternDetectVariable, $lessCurrentLine, $lessParserMatchParamName, PREG_OFFSET_CAPTURE);
                $detectImport = preg_match($patternDetectImport, $lessCurrentLine);

                //affection nom de la variable
                if ($detectVariable > 0) {
                    $lessParserParamName = $lessParserMatchParamName[0][0];
                } elseif ($detectImport > 0) {
                    $lessParserParamName = '@import';
                }

                //récupération de la valeur
                //position du début de la valeur
                $offsetVarStart = strlen($lessParserParamName);
                //position du point virgule dans la ligne courante
                $offsetVarEnd = strpos($lessCurrentLine, ';');

                //on vérifie que le point virgule est bien présent
                if (!($offsetVarEnd === false)) {
                    $lessParserParamValue = substr($lessCurrentLine, $offsetVarStart, $offsetVarEnd - $offsetVarStart);
                }

                //on supprime les caractÃ¨res encadrant du nom de la variable et de sa valeur
                $lessParserParamName = preg_replace('/@* *:*/', '', $lessParserParamName);
                $lessParserParamValue = preg_replace('/"*\'* */', '', $lessParserParamValue);

                //affection de l'option
                if ($detectVariable > 0) {
                    $options['type'] = 'variable';
					
					$lessParserParamValue = self::parseLessVariable($lessParserParamValue, $parameterValue);
					
                    //remplissage du tableau de valeurs
                    $parameterValue['variable'][$lessParserParamName] = $lessParserParamValue;
                } elseif ($detectImport > 0) {
                    $options['type'] = 'import';

                    //on supprime les espace en cas d'import
                    $lessParserParamValue = preg_replace('/ */', '', $lessParserParamValue);

                    //composition de l'URL totale
                    $compoImportURL = sfConfig::get('sf_web_dir') . '/theme/less/' . $lessParserParamValue;

                    //appel récursif de la fonction
                    $parameterValue = self::loadLessParameters(array(
                                //'type'		=> 'import',
                                'lessFile' => $compoImportURL
                                    ), $parameterValue
                    );
                }
            }
            //fermeture du fichier
            fclose($lessParserOpen);

            //retour du tableau de valeurs
            return $parameterValue;
        }
    }

    //fonction permettant de sortir la valeur d'un paramÃ¨tre less
    public static function getLessParam($variable = '') {
        $lessInitURL = sfConfig::get('sf_web_dir') . "/theme/less/_framework/SPLessCss/Config/_ConfigInit.less";
        $lessVariableImport = self::loadLessParameters(array(
                    'lessFile' => $lessInitURL
                ));

        return $lessVariableImport['variable'][$variable];
    }

    //fonction permettant d'importer la liste des imports des config du framework
    public static function printLessParams() {

        $lessInitURL = sfConfig::get('sf_web_dir') . "/theme/less/_framework/SPLessCss/Config/_ConfigInit.less";

        $lessVariableImport = self::loadLessParameters(array(
                    'lessFile' => $lessInitURL
                ));

        if (!($lessVariableImport === false)) {
            $counter = 0;
            foreach ($lessVariableImport['variable'] as $key => $value) {
                echo "&nbsp;&nbsp;&nbsp;&nbsp;" . $counter . " " . $key . " => " . $value . "<br/>";
                $counter++;
            }
        }
    }

	//calcul de la largeur d'un élément en fonction du nombre de colonnes passées en paramètre
	public static function gridGetWidth($nbreCols = 1, $padSub = 0){
		//récupération des valeurs de dimension de la grille
		$nbreCols = intval($nbreCols);
		$padSub = intval($padSub);
		$gridColWidth = intval(self::getLessParam('gridColWidth'));
		$gridGutter = intval(self::getLessParam('gridGutter'));
		//calcul des paramètres
		$elementWidth = $gridColWidth * $nbreCols + $gridGutter * ($nbreCols -1) - $padSub;

		return $elementWidth;
	}

	//calcul de la hauteur d'un élément en fonction du nombre de lignes passées en paramètre
	public static function gridGetHeight($nbreLine = 1, $padSub = 0) {
		//récupération des valeurs de dimension de la grille
		$nbreLine = intval($nbreLine);
		$padSub = intval($padSub);
		$gridBaseline = intval(self::getLessParam('gridBaseline'));
		//calcul des paramètres
		$elementHeight = ($gridBaseline * $nbreLine) - $padSub;

		return $elementHeight;
	}
	
	//calcul de la largeur du contenu
	public static function gridGetContentWidth(){
		//récupération du gabarit courant
		$currentGabarit = sfContext::getInstance()->getPage()->get('gabarit');
		if ($currentGabarit == 'default' || $currentGabarit == '') {
			$currentGabarit = self::getLessParam('templateGabarit');
		}

		//récupération des valeurs de colonnes
		$gridCol = self::lessCalculator(self::getLessParam('gridCol'));
		$gridCol_SidebarLeft = self::lessCalculator(self::getLessParam('gridCol_SidebarLeft'));
		$gridCol_SidebarRight = self::lessCalculator(self::getLessParam('gridCol_SidebarRight'));
		
		if($currentGabarit === 'sidebar-left'){
			$gridCol_Content = $gridCol - $gridCol_SidebarLeft;
		}elseif($currentGabarit === 'sidebar-right'){
			$gridCol_Content = $gridCol - $gridCol_SidebarRight;
		}elseif($currentGabarit === 'two-sidebars'){
			$gridCol_Content = $gridCol - $gridCol_SidebarLeft - $gridCol_SidebarRight;
		}elseif($currentGabarit === 'no-sidebar'){
			$gridCol_Content = $gridCol;
		}else{
			$gridCol_Content = $gridCol;
		}
		//calcul de la dimension du contenu
		$contentWidth = self::gridGetWidth($gridCol_Content);
		
		return $contentWidth;
	}

	//permet de supprimer les paragraphes
	public static function textEditorStripParagraph($inputText) {
		$filteredText = str_replace('<p>', '', $inputText);
		$filteredText = str_replace('</p>', '', $filteredText);
		return $filteredText;
	}
}