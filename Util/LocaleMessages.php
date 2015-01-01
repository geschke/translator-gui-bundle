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

    public function getFilename($bundle, $locale)
    {
        $path = $this->kernel->locateResource('@' . $bundle);

        $localeFiles = new LocaleFiles($this->kernel);

        $files = $localeFiles->getLanguages($path);
        $localeFile = null;

        // todo here: regard translation domains. default is "messages", but it stops at first match
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
        return $filename;
    }

    public function getMessage($bundle, $locale, $messageKey)
    {

        $path = $this->kernel->locateResource('@' . $bundle);

        $localeFiles = new LocaleFiles($this->kernel);

        $files = $localeFiles->getLanguages($path);
        $localeFile = null;

        // todo here: regard translation domains. default is "messages", but it stops at first match
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

        if (isset($messageArray[$messageKey])) {
            return $messageArray[$messageKey];
        }
        return false;
    }

    public function updateMessage($bundle, $locale, $messageKey, $translation)
    {
        // todo: support another translation formats, not only xliff...
        $container = $this->kernel->getContainer();
        $updater = $container->get('jms_translation.updater');
        $filename = $this->getFilename($bundle, $locale);
        // todo: check filename, does it exist?
//        $file = '/vol/www/test.geschke.net/htdocs/geschkenet/website/src/Geschke/Bundle/Admin/TranslatorGUIBundle/Resources/translations/messages.de.xliff';

        $updater->updateTranslation(
            $filename, 'xliff', 'messages', $locale, $messageKey,
            $translation
        );
        return true;
    }

}