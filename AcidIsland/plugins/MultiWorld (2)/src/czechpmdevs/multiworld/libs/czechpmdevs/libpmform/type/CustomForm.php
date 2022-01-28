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

class CustomForm extends Form {

    public function __construct(string $title, bool $ignoreInvalidResponse = false) {
        parent::__construct(Form::FORM_TYPE_CUSTOM, $ignoreInvalidResponse);

        $this->data["title"] = $title;
        $this->data["content"] = [];
    }

    /**
     * Adds text input to the custom form
     */
    public function addInput(string $text, ?string $defaultText = null, ?string $placeholder = null): void {
        $input = ["type" => "input", "text" => $text];
        if($defaultText !== null) {
            $input["default"] = $defaultText;
        }
        if($placeholder !== null) {
            $input["placeholder"] = $placeholder;
        }

        $this->data["content"][] = $input;
    }

    /**
     * Adds label (text) to the custom form
     */
    public function addLabel(string $text): void {
        $this->data["content"][] = ["type" => "label", "text" => $text];
    }

    /**
     * Adds toggle (switch) to the custom form
     */
    public function addToggle(string $text, ?bool $defaultValue = null): void {
        $toggle = ["type" => "toggle", "text" => $text];
        if($defaultValue !== null) {
            $toggle["default"] = $defaultValue;
        }

        $this->data["content"][] = $toggle;
    }

    /**
     * Adds dropdown (menu with options) to the form
     *
     * @param string[] $options
     */
    public function addDropdown(string $text, array $options, ?int $defaultOption = null): void {
        $dropdown = ["type" => "dropdown", "text" => $text, "options" => $options];
        if($defaultOption !== null) {
            $dropdown["default"] = $defaultOption;
        }

        $this->data["content"][] = $dropdown;
    }
}