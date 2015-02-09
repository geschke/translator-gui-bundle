<?php

/*
 * Copyright 2015 Ralf Geschke <ralf@kuerbis.org>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Geschke\Bundle\Admin\TranslatorGUIBundle\Controller;

use Geschke\Bundle\Admin\TranslatorGUIBundle\Entity\MessageTranslation;
use Geschke\Bundle\Admin\TranslatorGUIBundle\Entity\MessageTranslationResponse;
use Geschke\Bundle\Admin\TranslatorGUIBundle\Pagination\Paginator;
use Geschke\Bundle\Admin\TranslatorGUIBundle\Util\LocaleReference;
use JMS\TranslationBundle\Translation\Loader\XliffLoader;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

/**
 * The TranslationController handles all translation related functions
 *
 * @author geschke
 */
class TranslationController extends Controller
{

    /**
     * Show list of message by bundle, locale and domain
     * 
     * @param Request $request
     * @return type
     */
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
                $localeFile = $localeData;
                break;
            }
        }

        // build array with all current languages in the bundle
        $countMessageFiles = count($messageFiles);
        for ($i = 0; $i < $countMessageFiles; $i++) {
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

        $loader = new XliffLoader();
     
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

        $uri = $this->get('router')->generate('geschke_admin_translator_gui_translation_edit',
            array('bundle' => $bundle, 'locale' => $locale));
        $paginator->setBaseUrl($uri);

        $messageTranslation = new MessageTranslation();

        $form = $this->createFormBuilder($messageTranslation)
            ->add('locale', 'hidden')
            ->add('message_reference', 'textarea', array('disabled' => true, 'required' => false))
            ->add('message', 'textarea', array('disabled' => true))
            ->add('translation', 'textarea')
            ->getForm();

        $localeReference = new LocaleReference($request, $this->get('logger'));
        return $this->render('GeschkeAdminTranslatorGUIBundle:Language:list.html.twig',
            array(
                'mainnav' => '',
                'bundle' => $bundle,
                'locale' => $locale,
                'localeReference' => $localeReference->getLocaleReference(),
                'domain' => $domain,
                'messages' => $messages,
                'messageFiles' => $messageFiles,
                'form' => $form->createView(),
                'paginator' => $paginator

            ));
    }

    /**
     * Get message, translation and translation reference to show 
     * in edit formular
     * 
     * @param Request $request
     * @return Response
     */
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
            // todo: use session if available with fallback to english
            $localeReference = new LocaleReference($request, $this->get('logger'));
            
            $messageInstanceReference = $localeMessages->getMessage($bundle, $localeReference->getLocaleReference(), $messageId, $domain);

            if ($messageInstanceReference) {
                $messageRef = new MessageTranslation();
                $messageRef->setLocale($localeReference->getLocaleReference());
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

    /**
     * Store the submitted translation into message file
     * 
     * @param Request $request
     * @return Response
     */
    public function processTranslateAction(Request $request)
    {

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

    /**
     * Get message, translation and translation reference to show the 
     * message reference text
     * 
     * @param Request $request
     * @return Response
     */
    public function messageReferenceAction(Request $request)
    {
        $bundle = $request->get('bundle');
        $locale = $request->get('locale');
        $domain = $request->get('domain');
        $messageId = $request->get('messageId');

        $localeReference = new LocaleReference($request, $this->get('logger'));
        $localeReference->setLocaleReference($locale);

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
}
