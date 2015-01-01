<?php

namespace Geschke\Bundle\Admin\TranslatorGUIBundle\Controller;

use Geschke\Bundle\Admin\TranslatorGUIBundle\Entity\LanguageFile;
use Geschke\Bundle\Admin\TranslatorGUIBundle\Entity\MessageTranslation;
use Geschke\Bundle\Admin\TranslatorGUIBundle\Entity\MessageTranslationResponse;
use Geschke\Bundle\Admin\TranslatorGUIBundle\Util\LocaleDefinitions;
use Geschke\Bundle\Admin\TranslatorGUIBundle\Util\LocaleFiles;
use JMS\TranslationBundle\Translation\ConfigBuilder;
use JMS\TranslationBundle\Translation\Loader\XliffLoader;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
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

        $localeFiles = $this->container->get('geschke_bundle_admin_translatorguibundle.locale_files');

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
            ->add('message', 'textarea', array('disabled' => true))
            ->add('translation', 'textarea')

            //  ->add('dueDate', 'date')
           // ->add('save', 'submit', array('label' => $translator->trans("Create new language file")))
            ->getForm();




        return $this->render('GeschkeAdminTranslatorGUIBundle:Language:list.html.twig',
            array(
                'mainnav' => '',
                'bundle' => $bundle,
                'locale' => $locale,
                'messages' => $messageArray,
                'form' => $form->createView(),

            ));


    }

    public function rescanTranslationsAction(Request $request)
    {

        $bundle = $request->get('bundle');
        $locale = $request->get('locale');

       // var_dump($bundle);
        //var_dump($locale);

        $localeFiles = $this->container->get('geschke_bundle_admin_translatorguibundle.locale_files');
        $result = $localeFiles->rescanMessageFile($bundle, $locale);

        if ($result !== false) { // result returns number of found new messages or false, if failed
            $result = true;
        }
       // sleep(3);
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
            'success' => $result,
            'bundle' => $bundle,
            'locale' => $locale,
        ));

        return $response;
    }



    public function translateAction(Request $request)
    {
        $bundle = $request->get('bundle');
        $locale = $request->get('locale');
        $messageId = $request->get('messageId');

        // get message from file...
        // fill MessageTranslation object
        $encoders = array(new JsonEncoder());
        $normalizers = array(new GetSetMethodNormalizer());

        $serializer = new Serializer($normalizers, $encoders);
        $messageResponse = new MessageTranslationResponse();
        $messageResponse->setLocale($locale);
        $messageResponse->setBundle($bundle);

        $localeMessages = $this->container->get('geschke_bundle_admin_translatorguibundle.locale_messages');

        $messageInstance = $localeMessages->getMessage($bundle, $locale, $messageId);

        if ($messageInstance) {

            $message = new MessageTranslation();
            $message->setLocale($locale);
            $message->setMessage($messageInstance->getSourceString());
            $message->setTranslation($messageInstance->getLocaleString());

            $messageResponse->setSuccess(true);
            $messageResponse->setMessageTranslation($message);

        }
        else {

            $messageResponse->setSuccess(false);
        }
        $jsonContent = $serializer->serialize($messageResponse, 'json');
        $response = new Response();
        $response->setContent($jsonContent);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function processTranslateAction(Request $request)
    {
       // var_dump($request->get('locale'));
       // var_dump($request->get('bundle'));
        //var_dump($request->get('message'));
        //var_dump($request->get('translation'));

        $localeMessages = $this->container->get('geschke_bundle_admin_translatorguibundle.locale_messages');

        $success = $localeMessages->updateMessage($request->get('bundle'), $request->get('locale'), $request->get('message'), $request->get('translation'));


        $encoders = array(new JsonEncoder());
        $normalizers = array(new GetSetMethodNormalizer());

        $serializer = new Serializer($normalizers, $encoders);


        $messageResponse = new MessageTranslationResponse();
        $messageResponse->setLocale($request->get('locale'));
        $messageResponse->setBundle($request->get('bundle'));
        if ($success) {
            // or get translation from file?
            $message = new MessageTranslation();
            $message->setLocale($request->get('locale'));
            $message->setMessage($request->get('message'));
            $message->setTranslation($request->get('translation'));

            $messageResponse->setSuccess(true);
            $messageResponse->setMessageTranslation($message);

        }
        else {
            $messageResponse->setSuccess(false);
        }

        $jsonContent = $serializer->serialize($messageResponse, 'json');
        $response = new Response();
        $response->setContent($jsonContent);
        $response->headers->set('Content-Type', 'application/json');
        return $response;

    }

    }
