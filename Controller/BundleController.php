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

use Geschke\Bundle\Admin\TranslatorGUIBundle\Form\Type\LanguageChoiceType;
use Geschke\Bundle\Admin\TranslatorGUIBundle\Util\LocaleDefinitions;
use Geschke\Bundle\Admin\TranslatorGUIBundle\Util\LocaleFiles;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Description of BundleController
 *
 * @author geschke
 */
class BundleController extends Controller
{
      /**
     * Show the current registered bundles with language files
     * 
     * @return type
     */
    public function listBundlesAction()
    {
        $bundles = $this->container->getParameter('kernel.bundles');

        $kernel = $this->container->get('kernel');

        $translator = $this->get('translator');

        $csp_l10n_sys_locales = LocaleDefinitions::$csp_l10n_sys_locales;
        $csp_l10n_langs = LocaleDefinitions::$csp_l10n_langs;

        $localeFiles = new LocaleFiles($kernel);

        foreach ($bundles as $bundle => $bundleFullName) {
            $path = $kernel->locateResource('@' . $bundle);
            $messageFiles = $localeFiles->getLanguages($path);
            for ($i = 0; $i < count($messageFiles); $i++) {
              
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
            $bundleList[] = array(
                'name' => $bundle,
                'fullName' => $bundleFullName,
                'path' => $path,
                'messageFiles' => $messageFiles
            );
        }

        asort($bundleList);

        $form = $this->createForm(
            new LanguageChoiceType($this->container, $translator, false)
        );

        return $this->render('GeschkeAdminTranslatorGUIBundle:Bundles:list.html.twig',
            array(
                'mainnav' => 'bundles',
                'bundles' => $bundleList,
                'form_copy' => $form->createView()

            ));
    }
}
