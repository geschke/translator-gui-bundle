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


    public function getFilename($bundle, $locale, $domain)
    {
        $path = $this->kernel->locateResource('@' . $bundle);

        $localeFiles = new LocaleFiles($this->kernel);

        $files = $localeFiles->getLanguages($path);
        $localeFile = null;

        foreach ($files as $localeData) {
            if ($localeData['locale'] == $locale && $localeData['domain'] == $domain) {
                $localeFile = $localeData;
                break;
            }
        }

        // var_dump($path);
        // var_dump($localeFile);
        $filename = $path . 'Resources/translations/' . $localeFile['file'];
        return $filename;
    }

    public function getMessage($bundle, $locale, $messageKey, $domain = 'messages')
    {

        $filename = $this->getFilename($bundle, $locale, $domain);
        // todo maybe: load other than xliff files
        // switch format, if xlf or xliff...
//        $translator = $this->get('translator');

        //  $handle = fopen($filename,'r');
        $loader = new XliffLoader();
        //$foo = new XliffFileLoader();
        $messages = $loader->load($filename, $locale, $domain);


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
        $messageCollection = $r[$domain];
        $messageArray = $messageCollection->all();

        if (isset($messageArray[$messageKey])) {
            return $messageArray[$messageKey];
        }
        return false;
    }

    public function updateMessage($bundle, $locale, $messageKey, $translation, $domain = 'messages')
    {
        // todo: support another translation formats, not only xliff...
        $container = $this->kernel->getContainer();
        $updater = $container->get('jms_translation.updater');
        $filename = $this->getFilename($bundle, $locale, $domain);
        // todo: check filename, does it exist?
//        $file = '/vol/www/test.geschke.net/htdocs/geschkenet/website/src/Geschke/Bundle/Admin/TranslatorGUIBundle/Resources/translations/messages.de.xliff';

        $updater->updateTranslation(
            $filename, 'xliff', $domain, $locale, $messageKey,
            $translation
        );
        return true;
    }

}