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


namespace Geschke\Bundle\Admin\TranslatorGUIBundle\Extension;

class TwigExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('sha1', array($this, 'sha1Filter')),
        );
    }

    public function sha1Filter($string)
    {
        return sha1($string);
    }

    public function getName()
    {
        return 'geschke_translatorgui_bundle_twig_extension';
    }

}