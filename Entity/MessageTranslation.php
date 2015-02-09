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

class MessageTranslation
{
    protected $locale;
    protected $message;
    protected $messageReference;
    protected $translation;

    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message = null)
    {
        $this->message = $message;
    }

    public function getMessageReference()
    {
        return $this->messageReference;
    }

    public function setMessagereference($message = null)
    {
        $this->messageReference = $message;
    }

    public function getTranslation()
    {
        return $this->translation;
    }

    public function setTranslation($translation = null)
    {
        $this->translation = $translation;
    }


}