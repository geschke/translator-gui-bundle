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

use Geschke\Bundle\Admin\TranslatorGUIBundle\Entity\LanguageFile;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class LanguageController extends Controller
{

    /**
     * Add new language file handler
     * 
     * @param Request $request
     * @return type
     */
    public function addLanguageAction(Request $request)
    {
        $bundle = $request->get('bundle');
        $translator = $this->get('translator');

        $languageFile = new LanguageFile();
        $languageFile->setBundle($bundle);

        $form = $this->createForm('languagechoice', $languageFile);

        $form->handleRequest($request);
        if ($form->isValid()) {

            $locale = $languageFile->getLocale();
            $localeFiles = $this->container->get('geschke_bundle_admin_translatorguibundle.locale_files');

            $result = $localeFiles->rescanMessageFile($bundle, $locale);

            if ($result === false) {
                $this->get('session')->getFlashBag()->add(
                        'message_error', $translator->trans('message.languagefile_created_error')
                );
            } elseif ($result === 0) {
                $this->get('session')->getFlashBag()->add(
                        'message_warning', $translator->trans('message.languagefile_created_error_no_messages')
                );
            } else {
                // result > 0
                $this->get('session')->getFlashBag()->add(
                        'message_success', $translator->trans('message.languagefile_created_success') 
                );
            }
            return $this->redirect($this->generateUrl('geschke_admin_translator_gui_bundles'));
        }

        return $this->render('GeschkeAdminTranslatorGUIBundle:Default:language-add.html.twig', array(
                    'mainnav' => '',
                    'form' => $form->createView(),
                    'bundle' => $bundle,
        ));
    }

    /**
     * Delete language file action
     * 
     * @param Request $request
     * @return type
     */
    public function deleteLanguageAction(Request $request)
    {
        $bundle = $request->get('bundle');
        $locale = $request->get('locale');

        $translator = $this->get('translator');

        $localeFiles = $this->container->get('geschke_bundle_admin_translatorguibundle.locale_files');
        $result = $localeFiles->deleteMessageFile($bundle, $locale);


        if ($result) {
            $this->get('session')->getFlashBag()->add(
                    'message_success', $translator->trans('message.languagefile_deleted_success') 
            );
        } else {
            $this->get('session')->getFlashBag()->add(
                    'message_error', $translator->trans('message.languagefile_deleted_error') 
            );
        }

        return $this->redirect($this->generateUrl('geschke_admin_translator_gui_bundles'));
    }

    /**
     * Rescan and update a message file
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function rescanAction(Request $request)
    {

        $bundle = $request->get('bundle');
        $locale = $request->get('locale');

        $localeFiles = $this->container->get('geschke_bundle_admin_translatorguibundle.locale_files');
        $result = $localeFiles->rescanMessageFile($bundle, $locale);

        if ($result !== false) { // result returns number of found new messages or false, if failed
            $result = true;
        }
       
        $response = new JsonResponse();

        $response->setData(array(
            'success' => $result,
            'bundle' => $bundle,
            'locale' => $locale,
        ));

        return $response;
    }

    /**
     * Copy message file to a new locale
     * 
     * @param Request $request
     * @return type
     */
    public function processCopyAction(Request $request)
    {

        $bundle = $request->get('bundle');
        $localeFrom = $request->get('locale_from');
        $localeTo = $request->get('locale_to');
        $domain = $request->get('domain');

        // todo maybe some more filtering?
        $filterWarning = false;
        $localeToFiltered = preg_replace("/[^\w^\d^\-^_]/", '', $localeTo);
        if ($localeTo != $localeToFiltered) {
            // do the process with filtered string and display warning
            $filterWarning = true;
            $localeTo = $localeToFiltered;
        }

        $localeFiles = $this->container->get('geschke_bundle_admin_translatorguibundle.locale_files');
        $result = $localeFiles->copyMessageFile($bundle, $domain, $localeFrom, $localeTo);

        if ($result !== false) { // result returns number of found new messages or false, if failed
            $result = true;
        }

        $translator = $this->get('translator');

        if ($result === false) {
            $this->get('session')->getFlashBag()->add(
                    'message_error', $translator->trans('message.language_file_copy_error')
            );
        } elseif ($result === 0) {
            $this->get('session')->getFlashBag()->add(
                    'message_warning', $translator->trans('message.language_file_copy_warning') 
            );
        } else {
           
            $message = $filterWarning ? $translator->trans('message.language_file_copy_success_filtered') . $localeToFiltered : $translator->trans('message.language_file_copy_success');
            $this->get('session')->getFlashBag()->add(
                    'message_success', $message
            );
        }

        return $this->redirect($this->generateUrl('geschke_admin_translator_gui_bundles'));
    }

}
