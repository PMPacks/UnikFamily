<?php

/**
 * Copyright (C) 2021  CzechPMDevs
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */


declare(strict_types=1);

namespace czechpmdevs\multiworld\libs\czechpmdevs\libpmform\type;

use czechpmdevs\multiworld\libs\czechpmdevs\libpmform\Form;

class SimpleForm extends Form {

    public function __construct(string $title, string $content, bool $ignoreInvalidResponse = false) {
        parent::__construct(Form::FORM_TYPE_SIMPLE, $ignoreInvalidResponse);

        $this->data["title"] = $title;
        $this->data["content"] = $content;
    }

    public function addButton(string $text): void {
        $this->data["buttons"][] = ["text" => $text];
    }
}