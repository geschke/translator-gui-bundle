<?php
/**
 * Created by PhpStorm.
 * User: geschke
 * Date: 19.12.2014
 * Time: 00:46
 */


namespace Geschke\Bundle\Admin\TranslatorGUIBundle\Extension;

class TwigExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('sha1', array($this, 'sha1Filter')),
        );
    }

    public function sha1Filter($string)
    {
        return sha1($string);
    }

    public function getName()
    {
        return 'geschke_translatorgui_bundle_twig_extension';
    }

}