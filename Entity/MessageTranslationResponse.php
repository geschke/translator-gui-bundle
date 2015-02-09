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

namespace Geschke\Bundle\Admin\TranslatorGUIBundle\Entity;

class MessageTranslationResponse
{
    protected $messageTranslation;
    protected $messageTranslationReference;
    protected $success;
    protected $bundle;
    protected $locale;
    protected $domain;

    public function getSuccess()
    {
        return $this->success;
    }

    public function setSuccess($success = null)
    {
        $this->success = $success;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function setDomain($domain)
    {
        $this->domain = $domain;
    }


    public function getMessageTranslation()
    {
        return $this->messageTranslation;
    }

    public function setMessageTranslation(MessageTranslation $messageTranslation)
    {
        $this->messageTranslation = $messageTranslation;
    }

    public function getMessageTranslationReference()
    {
        return $this->messageTranslationReference;
    }

    public function setMessageTranslationReference(MessageTranslation $messageTranslation)
    {
        $this->messageTranslationReference = $messageTranslation;
    }



    public function getBundle()
    {
        return $this->bundle;
    }

    public function setBundle($bundle = null)
    {
        $this->bundle = $bundle;
    }


}