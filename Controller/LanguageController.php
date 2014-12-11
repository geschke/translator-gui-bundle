<?php

namespace Geschke\Bundle\Admin\TranslatorGUIBundle\Controller;

use Geschke\Bundle\Admin\TranslatorGUIBundle\Entity\LanguageFile;
use Geschke\Bundle\Admin\TranslatorGUIBundle\Entity\MessageTranslation;
use Geschke\Bundle\Admin\TranslatorGUIBundle\Util\LocaleDefinitions;
use Geschke\Bundle\Admin\TranslatorGUIBundle\Util\LocaleFiles;
use JMS\TranslationBundle\Translation\Loader\XliffLoader;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\Dumper\XliffFileDumper;
use Symfony\Component\Translation\Loader\XliffFileLoader;

class LanguageController extends Controller
{
    public function listTranslationsAction(Request $request)
    {

        $bundle = $request->get('bundle');
        $locale = $request->get('locale');

        $bundles = $this->container->getParameter('kernel.bundles');

        $kernel = $this->container->get('kernel');

        $path = $kernel->locateResource('@' . $bundle);

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
        $translator = $this->get('translator');

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
//var_dump($messageArray);
//        var_dump($messageKeys);
//die;
//        $translator->addLoader('xliff', new XliffFileLoader());
//        $translator->addResource('xliff', $filename, $locale);

        $messageTranslation = new MessageTranslation();

        $form = $this->createFormBuilder($messageTranslation)
            ->add('locale', 'hidden')
            ->add('message', 'text')
            ->add('translation', 'text')

            //  ->add('dueDate', 'date')
           // ->add('save', 'submit', array('label' => $translator->trans("Create new language file")))
            ->getForm();




        return $this->render('GeschkeAdminTranslatorGUIBundle:Language:list.html.twig',
            array(
                'mainnav' => '',
                'bundle' => $bundle,
                'messages' => $messageArray,
                'form' => $form->createView(),

            ));


    }

    public function rescanTranslationsAction(Request $request)
    {

        $bundle = $request->get('bundle');
        $locale = $request->get('locale');
        sleep(3);
        $response = new JsonResponse();
        /*
         {
success: false,
error: {
errorcode: "7y7",
displaymsg: "resource not found error"
}
}
         */
        $response->setData(array(
            'success' => true,
            'bundle' => $bundle,
            'locale' => $locale,
            'data' => 123
        ));

        return $response;
    }


}
