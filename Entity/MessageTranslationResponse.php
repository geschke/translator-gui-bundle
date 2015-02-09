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

/**
 * This is the response class needed for serializing answers of translation process
 */
class MessageTranslationResponse
{
    protected $messageTranslation;
    protected $messageTranslationReference;
    protected $success;
    protected $bundle;
    protected $locale;
    protected $domain;

    /**
     * Get success value
     * 
     * @return bool or null
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * Set success falue
     * 
     * @param bool $success
     */
    public function setSuccess($success = null)
    {
        $this->success = $success;
    }

    /**
     * Get locale value
     * 
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set locale value
     * 
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Get domain
     * 
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Set domain
     * 
     * @param string $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * Get message translation object
     * 
     * @return MessageTranslation
     */
    public function getMessageTranslation()
    {
        return $this->messageTranslation;
    }

    /**
     * Set message translation object
     * 
     * @param MessageTranslation $messageTranslation
     */
    public function setMessageTranslation(MessageTranslation $messageTranslation)
    {
        $this->messageTranslation = $messageTranslation;
    }

    /**
     * Get message translation reference object
     * 
     * @return MessageTranslation
     */
    public function getMessageTranslationReference()
    {
        return $this->messageTranslationReference;
    }

    /**
     * Set message translation reference object
     * 
     * @param MessageTranslation $messageTranslation
     */
    public function setMessageTranslationReference(MessageTranslation $messageTranslation)
    {
        $this->messageTranslationReference = $messageTranslation;
    }

    /**
     * Get bundle
     * 
     * @return string
     */
    public function getBundle()
    {
        return $this->bundle;
    }

    /**
     * Set bundle. 
     * 
     * @param string $bundle
     */
    public function setBundle($bundle = null)
    {
        $this->bundle = $bundle;
    }


}