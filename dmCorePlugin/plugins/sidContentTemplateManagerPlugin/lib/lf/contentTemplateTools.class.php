<?php

/**
 * class contentTemplateTools
 *
 */
class contentTemplateTools {

    public static $dumpExtension = 'dump';  // ATTENTION: utilisé dans l'installer.php
    public static $undesiredTables = array(// les tables non désirées dans le dump pour le template de contenu
        'dm_catalogue',
        'dm_contact_me', 
        'dm_error',
        'dm_group',
        'dm_group_permission',
        'dm_permission',
        'dm_remember_key',
        'dm_sent_mail',
        'dm_setting',
        'dm_setting_translation',
        'dm_trans_unit'
    );

    /**
     * 
     *   Retourne la config de la base de données dans un tableau avec les entrées:
     *  - user
     *  - pwd
     *  - dbname
     */
    public static function configBD() {
        $databaseConf = sfYaml::load(sfConfig::get('sf_config_dir') . '/databases.yml');
        $user = $databaseConf ['all']['doctrine']['param']['username'];
        $pwd = $databaseConf ['all']['doctrine']['param']['password'];
        $dsn = $databaseConf ['all']['doctrine']['param']['dsn'];

        if (preg_match('/dbname=.*;/', $dsn, $matches)) {   // on récupère le nom de la base de données à dumper, la base locale au site
            $dbname = str_replace('dbname=', '', $matches[0]);
            $dbname = str_replace(';', '', $dbname);
        } else {
            $return[$i]['ERROR'] = 'Impossible de récupérer le nom de la base dans config/database.yml';
        }

        if (preg_match('/host=.*;dbname/', $dsn, $matchesHost)) {   // on récupère le host de la base de données à dumper, la base locale au site
            $hostDb = str_replace('host=', '', $matchesHost[0]);
            $hostDb = str_replace(';dbname', '', $hostDb);
        } else {
            $return[$i]['ERROR'] = 'Impossible de récupérer le host de la base dans config/database.yml';
        }

        $config['user'] = $user;
        $config['pwd'] = $pwd;
        $config['dbname'] = $dbname;
        $config['dbhost'] = $hostDb;

        return $config;
    }

    /**
     * 
     * Effectue une sauvegarde des données de contenu d'un site, au format sql
     */
    public static function dumpDB($file) {

        $listTables = '';   // les tables à extraire
        // config base
        $config = self::configBD();
        $user = $config['user'];
        $pwd = $config['pwd'];
        $dbname = $config['dbname'];
        $dbhost = $config['dbhost'];

        // liste des tables de la base
        $dbTables = dmDb::pdo('SHOW TABLES')->fetchAll();  // toutes les tables
        
        // on enlève les tables undesired
        $i = 1;
        foreach ($dbTables as $dbTable) {
            if (!in_array($dbTable[0], self::$undesiredTables)) {
                $listTables .= $dbTable[0] . ' ';
                //echo $dbTable[0] . " ajoutée au dump \n";
                $i++;
            }
        }

        //$return[]['dumpDB'] = $listTables;
        // dump de la base
        $fileOUT = $file . "." . $dbname . "." . self::$dumpExtension;
        // option -c pour ajouter les champs dans la requete INSERT
        $output = exec("mysqldump -t -c --host=" . $dbhost . " --user=" . $user . " --password=" . $pwd . " " . $dbname . " " . $listTables . "> " . $fileOUT);
        $return[]['dumpDB'] = 'base ' . $dbname . ' -> ' . $fileOUT . '(' . filesize($fileOUT) . ' o)';

        // save du dossier uploads
        $dirOUTassets = $file . "." . $dbname . "." . self::$dumpExtension . ".assets";
        // le nom du dossier web
        $webDirName = substr(sfConfig::get('sf_web_dir'), strrpos(sfConfig::get('sf_web_dir'), '/') + 1);
        $output = exec("mkdir " . $dirOUTassets .";cp -R ". $webDirName . "/uploads " . $dirOUTassets ."/;");
        $return[]['dumpDB'] = 'copie des assets';

        // save du dossier apps/front/modules
        $dirOUTmodules = $file . "." . $dbname . "." . self::$dumpExtension . ".modules";
        $output = exec("mkdir " . $dirOUTmodules .";cp -R apps/front/modules " . $dirOUTmodules . "/;");
        $return[]['dumpDB'] = 'copie des modules du front';

        return $return;
    }

    /**
     * 
     *  Charge les données d'une sauvegarde effectuée par self::dumpDB
     */
    public static function loadDB($file) {

        $ext = substr($file, strlen($file) - strlen(self::$dumpExtension), strlen(self::$dumpExtension));
        if ($ext != self::$dumpExtension) {
            $return[]['ERROR'] = 'Le fichier : ' . $file . ' n\'a pas la bonne extension ' . self::$dumpExtension;
            return $return;
        }

        // config base
        $config = self::configBD();
        $user = $config['user'];
        $pwd = $config['pwd'];
        $dbname = $config['dbname'];
        $dbhost = $config['dbhost'];

        // truncate des futures tables à intégrer
        $i = 1;

        // mise en berne des clefs étrangères pour faire des truncate tranquilum...
        dmDb::pdo('SET FOREIGN_KEY_CHECKS = 0');

        // liste des tables de la base
        $dbTables = dmDb::pdo('SHOW TABLES')->fetchAll();  // toutes les tables
        
        // on vide les toutes les tables sauf les tables undesired
        $i = 1;
        foreach ($dbTables as $dbTable) {
            if (!in_array($dbTable[0], self::$undesiredTables)) {
                dmDb::pdo('TRUNCATE TABLE ' . $dbTable[0]);
                //$return[$i]['dumpDB'] = $dbTable[0] . ' vidée ';                
                $i++;
            }
        }        

        // mise en berne des clefs étrangères pour faire des truncate tranquilum...
        dmDb::pdo('SET FOREIGN_KEY_CHECKS = 1');

        // dump de la base
        $fileINdb = $file;
        // save du dossier uploads
        $dirINassets = $file . ".assets";
        // save du dossier uploads
        $dirINmodule = $file . ".modules";

        // load datas from DB
        $fileOUT = $file . "." . $dbname . "";
        // The '2>&1' is for redirecting errors to the standard IO (http://php.net/manual/fr/function.exec.php)
        // le '2>&1' est pour rediriger les erreur vers la sortie standard
        $command = 'mysql --host=' . $dbhost . ' --user=' . $user . ' --password=' . $pwd . ' ' . $dbname . ' < ' . $fileINdb . ' 2>&1';
        //$return[]['dumpDB'] = $command;

        $out = array();
        exec($command, $out);
//        print_r($out);

        if (count($out) == 0)
            $return[]['loadDB'] = $file . ' ---> BD ' . $dbname;

        foreach ($out as $outLine) {
            if (strpos($outLine, 'ERROR') === false) {
                // 
            } else {
                $return[]['ERROR'] = $outLine;
                $return[]['ERROR'] = 'Le fichier ' . $file . ' n\'est pas en cohérence avec la base ' . $dbname;
                $return[]['ERROR'] = 'Verifiez le modele de donnees du fichier ' . $file . ' avec le modele de donnees de la base ' . $dbname . '.';
            }
        }

        // load du dossier uploads
        // le dossier web
        $webDirName = substr(sfConfig::get('sf_web_dir'), strrpos(sfConfig::get('sf_web_dir'), '/') + 1);
        $output = exec("cp -R " . $dirINassets ."/* ". $webDirName . "/;");
        $return[]['loadDB'] = 'copie des assets';
        
        // load du dossier apps/front/modules
        $output = exec("cp -R " . $dirINmodule ."/* apps/front/;");
        $return[]['loadDB'] = 'copie des modules du front';        

        return $return;
    }

    // doctrine:data-dump et data-load abandonnés au profit de mysqldump
//        /**
//     * 
//     *
//     */
//    public static function parseFixtures() {
//        $fileIn = '/data/templatesContenu/demo2.yml';
//        $fileOut = '/data/templatesContenu/demo2_traite.yml';
//
//        // chargement du fichier d'entrée
//        $loader = sfYaml::load($fileIn);
//
//        // traitement du tableau $loader
//        $nonDesiredTables = array(    // les tables non désirées dans le chargement du template
////            'dm_contact_me',
////            'dm_error',
////            'dm_group',
////            'dm_group_permission',
////            'dm_mail_template',
////            'dm_mail_template_translation',
////            'dm_permission',
////            'dm_redirect',
////            'dm_remember_key',
////            'dm_sent_mail',
////            'dm_setting',
////            'dm_setting_translation',
////            'dm_trans_unit', 
//            'migration_version',
//
//            'sid_coord_name',
//            'sid_coord_name_version',
////            
////            'sid_blog_article',
////            'sid_blog_article_dm_tag',
////            'sid_blog_article_translation',
//            'sid_blog_article_translation_version',
////            'sid_blog_rubrique',
////            'sid_blog_rubrique_translation',
//            'sid_blog_rubrique_translation_version',
////            'sid_blog_type',
////            'sid_blog_type_article',
////            'sid_blog_type_translation',
//            'sid_blog_type_translation_version',
////            
////          'sid_bandeau',
////          'sid_bandeau_translation',
//            'sid_bandeau_translation_version',
////
////            'dm_media',            
////            'dm_media_folder',
//            
//        );
//
//        $i = 1;
//        foreach($nonDesiredTables as $nonDesiredTable){
//            unset($loader[dmString::camelize($nonDesiredTable)]);
//            $return[$i]['table non desirees'] = $nonDesiredTable;
//            $i++;
//        }
//        
//        // enregistrement dans le fichier de sortie
//        $yaml = sfYaml::dump($loader);
//        file_put_contents($fileOut, $yaml);
//
//        $return[0]['parseFixtures'] = $fileIn.' -> '.$fileOut.' OK';
//
//        return $return;
//    }
//    
//        /**
//     * 
//     *
//     */
//    public static function loadFixtures() {
//        
//        // ne pas charger les version
//        // créer les dossiers (mkdir) de dmMediaFolder sous upload
//        // ajouter http://www.doctrine-project.org/documentation/manual/1_2/data-fixtures/data-fixtures#fixtures-for-nested-sets
//        // 
// 
//    }
}

