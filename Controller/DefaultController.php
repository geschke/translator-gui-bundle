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
use Geschke\Bundle\Admin\TranslatorGUIBundle\Form\Type\LanguageChoiceType;
use Geschke\Bundle\Admin\TranslatorGUIBundle\Util\LocaleDefinitions;
use Geschke\Bundle\Admin\TranslatorGUIBundle\Util\LocaleFiles;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * 
 */
class DefaultController extends Controller
{
    /**
     * Show welcome screen
     * 
     * @param Request $request
     * @return type
     */
    public function indexAction(Request $request)
    {

        return $this->render('GeschkeAdminTranslatorGUIBundle:Default:index.html.twig',
            array(
                'mainnav' => 'index',
            ));
    }


 

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
                    'message_error',
                    $translator->trans('message.languagefile_created_error') # Error by creating language file.
                );
            }
            elseif ($result === 0) {
                $this->get('session')->getFlashBag()->add(
                    'message_warning',
                    $translator->trans('message.languagefile_created_error_no_messages') # No messages found. The language file was not created.
                );
            } else {
                // result > 0
                $this->get('session')->getFlashBag()->add(
                    'message_success',
                    $translator->trans('message.languagefile_created_success') # 'The language file was created successfully.'
                );
            }
            return $this->redirect($this->generateUrl('geschke_admin_translator_gui_bundles'));
        }

        return $this->render('GeschkeAdminTranslatorGUIBundle:Default:language-add.html.twig',
            array(
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
                'message_success',
                $translator->trans('message.languagefile_deleted_success') # the language file was deleted successfully
            );

        } else {
            $this->get('session')->getFlashBag()->add(
                'message_error',
                $translator->trans('message.languagefile_deleted_error') # error by deleting language file
            );
        }

        return $this->redirect($this->generateUrl('geschke_admin_translator_gui_bundles'));

    }

 }
