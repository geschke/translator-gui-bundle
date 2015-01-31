<?php

namespace Geschke\Bundle\Admin\TranslatorGUIBundle\Controller;

use Geschke\Bundle\Admin\TranslatorGUIBundle\Entity\MessageTranslation;
use Geschke\Bundle\Admin\TranslatorGUIBundle\Entity\MessageTranslationResponse;
use Geschke\Bundle\Admin\TranslatorGUIBundle\Pagination\Paginator;
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
        $domain = $request->get('domain');
        if (!$domain) {
            $domain = 'messages';
        }

        $bundles = $this->container->getParameter('kernel.bundles');

        $kernel = $this->container->get('kernel');

        $path = $kernel->locateResource('@' . $bundle);

        $localeFiles = $this->container->get('geschke_bundle_admin_translatorguibundle.locale_files');

        $messageFiles = $localeFiles->getLanguages($path);
        $localeFile = null;
        foreach ($messageFiles as $localeData) {

            if ($localeData['locale'] == $locale && $localeData['domain'] == $domain) {
                //echo "locale file found!";
                $localeFile = $localeData;
                break;
            }
        }

        // build array with all current languages in the bundle
        $countMessageFiles = count($messageFiles);
        for ($i = 0; $i < $countMessageFiles; $i++) {
            //$messageFiles[$i]['additional'] = array();
            if ($messageFiles[$i]['domain'] != $domain) {
                unset($messageFiles[$i]);
                continue;
            }
            if (!isset($csp_l10n_sys_locales[$messageFiles[$i]['locale']]) &&
                !isset($csp_l10n_langs[$messageFiles[$i]['locale']])) {
                $messageFiles[$i]['unknown'] = true;
            }
            if (strlen($messageFiles[$i]['locale']) > 2) {
                if (isset($csp_l10n_sys_locales[$messageFiles[$i]['locale']])) {
                    $messageFiles[$i]['additional'] = $csp_l10n_sys_locales[$messageFiles[$i]['locale']];
                }
            }
        }

        $filename = $path . 'Resources/translations/' . $localeFile['file'];
        // todo maybe: load other than xliff files
        // switch format, if xlf or xliff...
        $translator = $this->get('translator');

        //  $handle = fopen($filename,'r');
        $loader = new XliffLoader();
        //$foo = new XliffFileLoader();
        $messages = $loader->load($filename, $locale);


        $reflect = new \ReflectionClass($messages);

        $refDomains = $reflect->getProperty('domains');
        $refDomains->setAccessible(true);

        $r = $refDomains->getValue($messages);
        $messageCollection = $r['messages'];
        $messageArray = $messageCollection->all();

        $cnt = count($messageArray);

        $page = $request->get('page') ? $request->get('page') : 1;
        $itemsPerPage = 25;

        $paginator = new Paginator($page, $cnt, $itemsPerPage);

        $offset = $paginator->getOffset();

        $messages = array_slice($messageArray, $offset, $itemsPerPage);


        $uri = $this->get('router')->generate('geschke_admin_translator_gui_language_edit',
            array('bundle' => $bundle, 'locale' => $locale));
        $paginator->setBaseUrl($uri);

        $messageTranslation = new MessageTranslation();

        $form = $this->createFormBuilder($messageTranslation)
            ->add('locale', 'hidden')
            ->add('message_reference', 'textarea', array('disabled' => true, 'required' => false))
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
                'localeReference' => $this->getLocaleReference($request),
                'domain' => $domain,
                'messages' => $messages,
                'messageFiles' => $messageFiles,
                'form' => $form->createView(),
                'paginator' => $paginator

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

        $response->setData(array(
            'success' => $result,
            'bundle' => $bundle,
            'locale' => $locale,
        ));

        return $response;
    }

    protected function setLocaleReference(Request $request, $locale)
    {
        $logger = $this->get('logger');
        $logger->info('set locale to ' . $locale);

        $session = $request->getSession();
        $session->set('translator-gui-bundle-localeReference', $locale);
        return true;
    }

    protected function getLocaleReference(Request $request)
    {
        $session = $request->getSession();
        $localeReference = $session->get('translator-gui-bundle-localeReference');
        if (!$localeReference) {
            $localeReference = 'en';
        }
        $this->setLocaleReference($request, $localeReference);
        $logger = $this->get('logger');
        $logger->info('get locale reference with value  ' . $localeReference);


        return $localeReference;
    }


    public function messageReferenceAction(Request $request)
    {
        $bundle = $request->get('bundle');
        $locale = $request->get('locale');
        $domain = $request->get('domain');
        $messageId = $request->get('messageId');

        $this->setLocaleReference($request, $locale);

      /*  var_dump($messageId);
        var_dump($domain);
        var_dump($bundle);
        var_dump($locale);
        die;
*/
        // get message from file...
        // fill MessageTranslation object
        $encoders = array(new JsonEncoder());
        $normalizers = array(new GetSetMethodNormalizer());

        $serializer = new Serializer($normalizers, $encoders);
        $messageResponse = new MessageTranslationResponse();
        $messageResponse->setLocale($locale);
        $messageResponse->setBundle($bundle);
        $messageResponse->setDomain($domain);

        $localeMessages = $this->container->get('geschke_bundle_admin_translatorguibundle.locale_messages');

        $messageInstance = $localeMessages->getMessage($bundle, $locale, $messageId, $domain);

        if ($messageInstance) {

            $message = new MessageTranslation();
            $message->setLocale($locale);
            $message->setMessage($messageInstance->getSourceString());
            $message->setTranslation($messageInstance->getLocaleString());

            $messageResponse->setSuccess(true);
            $messageResponse->setMessageTranslation($message);

        } else {

            $messageResponse->setSuccess(false);
        }
        $jsonContent = $serializer->serialize($messageResponse, 'json');
        $response = new Response();
        $response->setContent($jsonContent);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }


    public function translateAction(Request $request)
    {
        $bundle = $request->get('bundle');
        $locale = $request->get('locale');
        $domain = $request->get('domain');
        $messageId = $request->get('messageId');

        // get message from file...
        // fill MessageTranslation object
        $encoders = array(new JsonEncoder());
        $normalizers = array(new GetSetMethodNormalizer());

        $serializer = new Serializer($normalizers, $encoders);
        $messageResponse = new MessageTranslationResponse();
        $messageResponse->setLocale($locale);
        $messageResponse->setBundle($bundle);
        $messageResponse->setDomain($domain);

        $localeMessages = $this->container->get('geschke_bundle_admin_translatorguibundle.locale_messages');

        $messageInstance = $localeMessages->getMessage($bundle, $locale, $messageId, $domain);

        if ($messageInstance) {

            $message = new MessageTranslation();
            $message->setLocale($locale);
            $message->setMessage($messageInstance->getSourceString());
            $message->setTranslation($messageInstance->getLocaleString());

            // load message translation reference, ar first it will be english language
            $messageInstanceReference = $localeMessages->getMessage($bundle, 'en',  $messageId, $domain);

            // just hardcoded here
            // todo: if file not found, don't fail hardly

            if ($messageInstanceReference) {
                $messageRef = new MessageTranslation();
                $messageRef->setLocale('en');
                $messageRef->setMessage($messageInstanceReference->getSourceString());
                $messageRef->setTranslation($messageInstanceReference->getLocaleString());
                $messageResponse->setMessageTranslationReference($messageRef);

            }

            $messageResponse->setSuccess(true);
            $messageResponse->setMessageTranslation($message);

        } else {

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

        $success = $localeMessages->updateMessage($request->get('bundle'), $request->get('locale'), $request->get('message'), $request->get('translation'), $request->get('domain'));


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

        } else {
            $messageResponse->setSuccess(false);
        }

        $jsonContent = $serializer->serialize($messageResponse, 'json');
        $response = new Response();
        $response->setContent($jsonContent);
        $response->headers->set('Content-Type', 'application/json');
        return $response;

    }


    public function processCopyAction(Request $request)
    {

        $bundle = $request->get('bundle');
        $localeFrom = $request->get('locale_from');
        $localeTo = $request->get('locale_to');
        $domain = $request->get('domain');
        
        $translator = $this->get('translator');

        $result = true;
        if ($result === false) {
            $this->get('session')->getFlashBag()->add(
                'message_error',
                $translator->trans('Error by copying language file.')
            );
        }
        elseif ($result === 0) {
            $this->get('session')->getFlashBag()->add(
                'message_warning',
                $translator->trans('Something happened. Don\'t know more yet.')
            );
        } else {
            // result > 0
            $this->get('session')->getFlashBag()->add(
                'message_success',
                'The language file was copied successfully.'
            );
        }
      //  die("hard");
        return $this->redirect($this->generateUrl('geschke_admin_translator_gui_bundles'));




    }


}
