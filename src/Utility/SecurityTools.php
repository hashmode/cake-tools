<?php
namespace CakeTools\Utility;

use Cake\Utility\Security;
use Cake\Core\Configure;

/**
 * SecurityTools
 */
class SecurityTools
{

    /**
     * encrypt method
     *
     * @param string $plain            
     * @return string
     */
    public static function encrypt($plain)
    {
        return base64_encode(Security::encrypt($plain, Configure::read('CakeTools.security.app_key'), ''));
    }

    /**
     * decrypt method
     *
     * @param string $cipher            
     * @return string
     */
    public static function decrypt($cipher)
    {
        return Security::decrypt(base64_decode($cipher), Configure::read('CakeTools.security.app_key'), '');
    }
}
