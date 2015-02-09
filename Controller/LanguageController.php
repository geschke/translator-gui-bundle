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


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

class LanguageController extends Controller
{
   
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

  
  

    public function processCopyAction(Request $request)
    {

        $bundle = $request->get('bundle');
        $localeFrom = $request->get('locale_from');
        $localeTo = $request->get('locale_to');
        $domain = $request->get('domain');
        
        // todo maybe some more filtering?
        $filterWarning = false;
        $localeToFiltered = preg_replace("/[^\w^\d^\-^_]/",'',$localeTo);    
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
                'message_error',
                $translator->trans('message.language_file_copy_error') // Error by copying language file.')
            );
        }
        elseif ($result === 0) {
            $this->get('session')->getFlashBag()->add(
                'message_warning',
                $translator->trans('message.language_file_copy_warning') //Something happened. Don\'t know more yet.')
            );
        } else {
            // result > 0
            //$message =  $filterWarning ? $translator->trans('The language file was copied successfully, but the locale is filtered as ') . $localeToFiltered : $translator->trans('The language file was copied successfully.'); 
            $message =  $filterWarning ? $translator->trans('message.language_file_copy_success_filtered') . $localeToFiltered : $translator->trans('message.language_file_copy_success'); 
            $this->get('session')->getFlashBag()->add(
                'message_success',
                    $message
            );
        }
   
        return $this->redirect($this->generateUrl('geschke_admin_translator_gui_bundles'));

    }

}
