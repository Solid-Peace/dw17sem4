<?php   

/**
 * Class Autoloader
 */
class Autoloader{

    /**
     * Enregistre notre autoloader
     */
    static function register(){
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    /**
     * Inclue le fichier correspondant à notre classe
     * @param $class string Le nom de la classe à charger
     */
    static function autoload($class){
        try {
            require 'class/' . $class . '.php';
        } catch (Exception $e) {
            echo 'pas de classe : '. $e->getMessage() .' essayons le suivant ... <br>';
        }
        
        require 'vendor/autoload.php';
    }

}


 ?>