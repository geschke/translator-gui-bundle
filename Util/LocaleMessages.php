<?php
/**
 * Created by PhpStorm.
 * User: geschke
 * Date: 17.12.2014
 * Time: 22:19
 */

namespace Geschke\Bundle\Admin\TranslatorGUIBundle\Util;

use Symfony\Component\DependencyInjection\ContainerAware;
use JMS\TranslationBundle\Translation\Loader\XliffLoader;

class LocaleMessages extends ContainerAware
{

    private $kernel;
  //  private $translator;

    public function __construct($kernel)
    {
        $this->kernel = $kernel;
//        $this->translator = $translator;
    }

    public function getMessage($bundle, $locale, $messageKey)
    {

        $path = $this->kernel->locateResource('@' . $bundle);

        $localeFiles = new LocaleFiles();

        $files = $localeFiles->getLanguages($path);
        $localeFile = null;
        foreach ($files as $localeData) {
            if ($localeData['locale'] == $locale) {
                //echo "locale file found!";
                $localeFile = $localeData;
                break;
            }
        }

        // var_dump($path);
        // var_dump($localeFile);
        $filename = $path . 'Resources/translations/' . $localeFile['file'];
        // todo maybe: load other than xliff files
        // switch format, if xlf or xliff...
//        $translator = $this->get('translator');

        //  $handle = fopen($filename,'r');
        $foo = new XliffLoader();
        //$foo = new XliffFileLoader();
        $messages = $foo->load($filename, $locale);


        $reflect = new \ReflectionClass($messages);
        //      $props   = $reflect->getProperties();


        /*  foreach ($props as $prop) {
              print $prop->getName() . "\n";
          }
  */
        // var_dump($props);

        $refDomains = $reflect->getProperty('domains');
        $refDomains->setAccessible(true);

        $r = $refDomains->getValue($messages);
        $messageCollection = $r['messages'];
        $messageArray = $messageCollection->all();

        //var_dump($messageArray);
        foreach ($messageArray as $key => $message) {
            $messageKeys[] = $key;
        }
        var_dump($messageKeys);
        return true;
    }

}