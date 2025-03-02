<?php

/**
 * A static class to generate various Bootstrap 5.3 UI elements.
 * 
 * Example usage:
 * 
 *   echo BootstrapElements::textarea(id: "myText", label: "My Label", placeholder: "Write something...");
 *   echo BootstrapElements::singleInputBox(id: "myInput", label: "Name");
 *   echo BootstrapElements::modal(id: "myModal", title: "Welcome", body: "Hello World!");
 */
class Boot
{
    /**
     * Generates a random string of length $length. 
     * If $lettersOnly is true, the string is alphabetic only.
     *
     * @param int  $length
     * @param bool $lettersOnly
     * @return string
     */
    private static function generateRandomString(int $length = 10, bool $lettersOnly = false): string
    {
        // A more secure approach using random_bytes
        $characters = $lettersOnly
            ? 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
            : '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $bytes    = random_bytes($length);
        $result   = '';
        $charLen  = strlen($characters);

        for ($i = 0; $i < $length; $i++) {
            $result .= $characters[ord($bytes[$i]) % $charLen];
        }

        return $result;
    }

    /**
     * Generate a Bootstrap textarea.
     *
     * @param string $id
     * @param string $label
     * @param string $placeholder
     * @param int    $rows
     * @param string $value
     * @param bool   $hide
     * @param bool   $required
     * @return string
     */
    public static function textarea(
        string $id = "",
        string $label = "",
        string $placeholder = "",
        int    $rows = 3,
        string $value = "",
        bool   $hide = false,
        bool   $required = false
    ): string {
        $style    = $hide ? 'style="display:none;"' : '';
        $requiredAttr = $required ? 'required' : '';
        
        $labelHtml = $label !== "" 
            ? '<label class="form-label" for="' . $id . 'TAI">' . $label . '</label>' 
            : '';

        return <<<HTML
<div id="{$id}TAC" class="mb-3" {$style}>
    {$labelHtml}
    <textarea class="form-control" id="{$id}TAI" rows="{$rows}" placeholder="{$placeholder}" {$requiredAttr}>{$value}</textarea>
</div>
HTML;
    }

    /**
     * Generate a single Bootstrap input box.
     *
     * @param string      $id
     * @param string      $placeholder
     * @param bool|string $require    If 'y', required attribute is set
     * @param string      $css        Additional CSS classes
     * @param string      $label
     * @param string      $type       [text|number|password|...]
     * @param string      $value
     * @param bool        $disabled
     * @param bool        $hidden
     * @param bool        $numbersOnly
     * @param string      $label2
     * @param string      $dataAttr   Additional data-* attributes
     * @return string
     */
    public static function singleInputBox(
        string  $id = '',
        string  $placeholder = '',
        mixed   $require = '',
        string  $css = '',
        string  $label = '',
        string  $type = '',
        string  $value = '',
        bool    $disabled = false,
        bool    $hidden = false,
        bool    $numbersOnly = false,
        string  $label2 = '',
        string  $dataAttr = ''
    ): string {
        // Determine if required
        $requiredAttr = ($require === 'y') ? 'required' : '';

        // Determine type
        if ($numbersOnly) {
            $type = 'number';
        } elseif (empty($type)) {
            $type = 'text';
        }
        
        $disabledAttr = $disabled ? 'disabled' : '';
        $hiddenStyle  = $hidden   ? 'display:none;' : '';

        $labelHtml  = $label  !== '' ? '<span class="input-group-text text-wrap">' . $label  . '</span>' : '';
        $label2Html = $label2 !== '' ? '<span class="input-group-text text-wrap">' . $label2 . '</span>' : '';

        $dataAttrHtml = $dataAttr ? 'data-attr="' . htmlspecialchars($dataAttr) . '"' : '';

        return <<<HTML
<div style="{$hiddenStyle}" class="input-group mb-3 singleInputBox {$css}">
    {$labelHtml} 
    {$label2Html}
    <input id="{$id}" type="{$type}" class="form-control" placeholder="{$placeholder}" 
           {$requiredAttr} value="{$value}" {$disabledAttr} autocomplete="off" {$dataAttrHtml}>
</div>
HTML;
    }

    /**
     * Generate a Bootstrap date picker input.
     *
     * @param string $id
     * @param string $placeholder
     * @param bool|string $require
     * @param string $css
     * @param string $label
     * @param string $value
     * @param bool   $disabled
     * @param bool   $removeDay  If true, uses type="month" instead of "date"
     * @return string
     */
    public static function datePicker(
        string  $id = '',
        string  $placeholder = '',
        mixed   $require = '',
        string  $css = '',
        string  $label = '',
        string  $value = '',
        bool    $disabled = false,
        bool    $removeDay = false
    ): string {
        $requiredAttr = ($require === 'y') ? 'required' : '';
        $disabledAttr = $disabled ? 'disabled' : '';
        $type         = $removeDay ? 'month' : 'date';

        $labelHtml = $label !== '' 
            ? '<span class="input-group-text text-wrap">' . $label . '</span>' 
            : '';

        $maxDate = date('Y-m-d');

        return <<<HTML
<div class="input-group mb-3 datePicker {$css}">
    {$labelHtml}
    <input id="{$id}" type="{$type}" class="form-control" 
           placeholder="{$placeholder}" {$requiredAttr} value="{$value}" 
           {$disabledAttr} max="{$maxDate}">
</div>
HTML;
    }

    /**
     * Generate a Bootstrap modal with optional trigger button.
     *
     * @param string $id
     * @param string $title
     * @param string $body
     * @param string $footer
     * @param bool   $buttonVisible  If true, shows a button that triggers the modal
     * @param string $buttonName
     * @param string $buttonClass
     * @param string $size           [sm|lg|xl]
     * @param string $actionButtonName
     * @param string $closeText
     * @param bool   $showCloseButton
     * @param bool   $disabled
     * @param string $actionButtonStyle
     * @param bool   $actionButtonDisableClose
     * @param string $titleCSS
     * @return string
     */
    public static function modal(
        string $id = '',
        string $title = '',
        string $body = '',
        string $footer = '',
        bool   $buttonVisible = false,
        string $buttonName = 'Open Modal',
        string $buttonClass = 'btn-primary',
        string $size = 'xl',
        string $actionButtonName = "Save",
        string $closeText = "Close",
        bool   $showCloseButton = true,
        bool   $disabled = false,
        string $actionButtonStyle = "",
        bool   $actionButtonDisableClose = false,
        string $titleCSS = ''
    ): string {
        // Generate random ID if not provided
        if (empty($id)) {
            $id = self::generateRandomString(8, true);
        }

        $disabledAttr = $disabled ? 'disabled' : '';
        
        // Decide if action button closes the modal
        $actionButtonCloseAttr = $actionButtonDisableClose ? '' : 'data-bs-dismiss="modal"';

        // Modal trigger button
        $buttonHtml = '';
        if ($buttonVisible) {
            $buttonHtml = <<<HTML
<button id="modalButton-{$id}" type="button" class="btn {$buttonClass}" data-bs-toggle="modal" data-bs-target="#{$id}" {$disabledAttr}>
    {$buttonName}
</button>
HTML;
        }

        // Close button
        $closeButtonHtml = '';
        if ($showCloseButton) {
            $closeButtonHtml = <<<HTML
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{$closeText}</button>
HTML;
        }

        // Modal size
        $modalSizeClass = "modal-{$size}";

        // Optional custom footer or default
        $footerHtml = $footer ?: <<<HTML
{$closeButtonHtml}
<button id="action-{$id}" type="button" class="btn btn-primary {$actionButtonStyle}" {$actionButtonCloseAttr}>
    {$actionButtonName}
</button>
HTML;

        return <<<HTML
{$buttonHtml}

<div class="modal fade" id="{$id}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog {$modalSizeClass}">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title {$titleCSS}">{$title}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        {$body}
      </div>
      <div class="modal-footer">
        {$footerHtml}
      </div>
    </div>
  </div>
</div>
HTML;
    }

    /**
     * Generate a combined single input box with attached dropdown.
     *
     * @param string      $id
     * @param array       $options        Key-value array for dropdown
     * @param string      $placeholder
     * @param string      $label
     * @param string      $selectValue
     * @param string      $inputValue
     * @param string      $dropdownGroupStyle
     * @param bool        $inputDisabled
     * @param bool        $numbersOnly
     * @param string      $dropdownClass
     * @param string      $inputClass
     * @param string|bool $require
     * @return string
     */
    public static function singleInputBoxDropDown(
        string $id = '',
        array  $options = [],
        string $placeholder = "",
        string $label = "",
        string $selectValue = "",
        string $inputValue = "",
        string $dropdownGroupStyle = "",
        bool   $inputDisabled = false,
        bool   $numbersOnly = false,
        string $dropdownClass = "",
        string $inputClass = "",
        mixed  $require = ''
    ): string {
        if (empty($options)) {
            // Or throw exception
            return '<!-- No options array provided -->';
        }

        // Build the dropdown and capture as string
        $dropdownHtml = self::dropdown(
            id:                  'groupDropdown_' . $id,
            options:             $options,
            selectValue:         $selectValue,
            dropdownClass:       'groupDropdown ' . $dropdownClass,
            dropdownGroupStyle:  $dropdownGroupStyle,
            require:             $require
        );

        // Combine dropdown + input
        $inputHtml = self::singleInputBox(
            id:          'groupInput_' . $id,
            label:       $label,
            label2:      $dropdownHtml,   // Insert dropdown in second label span
            placeholder: $placeholder,
            value:       $inputValue,
            disabled:    $inputDisabled,
            numbersOnly: $numbersOnly,
            require:     $require,
            css:         $inputClass
        );

        return <<<HTML
<div class="groupContainer_{$id} groupContainer">
    {$inputHtml}
</div>
HTML;
    }

    /**
     * Generate a standard Bootstrap select (dropdown).
     *
     * @param string       $id
     * @param string       $label
     * @param array        $options
     * @param string       $selectValue
     * @param string       $selectTextValue
     * @param string       $dropdownClass
     * @param string       $dropdownGroupClass
     * @param string       $dropdownGroupStyle
     * @param string       $disabled
     * @param bool|string  $require
     * @param bool         $hidden
     * @param string       $defaultText
     * @return string
     */
    public static function dropdown(
        string $id = '',
        string $label = '',
        array  $options = [],
        string $selectValue = '',
        string $selectTextValue = '',
        string $dropdownClass = '',
        string $dropdownGroupClass = '',
        string $dropdownGroupStyle = '',
        string $disabled = '',
        mixed  $require = '',
        bool   $hidden = false,
        string $defaultText = 'Please select'
    ): string {
        $disabledAttr = ($disabled === 'y') ? 'disabled' : '';
        $requiredAttr = (!empty($require))  ? 'required' : '';
        $hiddenStyle  = $hidden ? 'display:none;' : '';

        $labelHtml = $label !== '' 
            ? '<span class="input-group-text">' . $label . '</span>' 
            : '';

        $html = <<<HTML
<div style="{$hiddenStyle} {$dropdownGroupStyle}" class="input-group mb-3 dropdown {$dropdownGroupClass}">
    {$labelHtml}
    <select id="{$id}" name="{$id}" class="form-select {$dropdownClass}" {$disabledAttr} {$requiredAttr}>
HTML;

        // Default option
        if ($selectValue === '') {
            $html .= "<option value=\"\">{$defaultText}</option>";
        }

        // Loop over key=>value
        foreach ($options as $key => $value) {
            $selected = '';
            if (
                (!empty($selectValue) && trim($key) === trim($selectValue)) ||
                (!empty($selectTextValue) && trim($value) === trim($selectTextValue))
            ) {
                $selected = 'selected';
            }
            $html .= "<option value=\"{$key}\" {$selected}>{$value}</option>";
        }

        $html .= <<<HTML
    </select>
</div>
HTML;

        return $html;
    }

    /**
     * Generate radio or checkbox groups.
     * 
     * Special keys in $data:
     *  - '_id' => custom group ID
     *  - '_checkedValue' => sets which input is checked by default
     *  - '_question' => prepended text
     *  - '_customAttrs' => custom attributes for <input>
     *  - '_layout' => [stacked|inlineTop|group|onerow|list] 
     *
     * @param array  $data
     * @param string $type  's' => single choice (radio), 'm' => multiple choice (checkbox)
     * @return string
     */
    public static function checkboxes(array $data = [], string $type = 's'): string
    {
        // Type: s => radio, m => checkbox
        $typeAttr = match ($type) {
            's' => 'radio',
            'm' => 'checkbox',
            default => 'checkbox'
        };

        if (!is_array($data)) {
            return '<!-- checkboxes(): $data must be an array -->';
        }

        $id           = $data['_id']           ?? uniqid('chk_');
        $checkedValue = $data['_checkedValue'] ?? "";
        $question     = $data['_question']     ?? "";
        $customAttrs  = $data['_customAttrs']  ?? "";
        $layout       = $data['_layout']       ?? 'inline';
        
        unset($data['_id'], $data['_checkedValue'], $data['_question'], $data['_customAttrs'], $data['_layout']);

        // A helper closure for building a single input
        $renderInput = function($key, $option, $name, $checked = false, $requiredAttr = '') use ($typeAttr, $customAttrs, $id) {
            $checkedAttr = $checked ? 'checked' : '';
            return <<<HTML
<span class="checkboxOption">
  <span>{$option}</span>
  <label class="checkboxContainer">
    <input type="{$typeAttr}" name="{$name}" value="{$key}" id="{$id}" {$checkedAttr} {$customAttrs} {$requiredAttr}>
    <span class="checkmark"></span>
  </label>
</span>
HTML;
        };

        // For multiple checkboxes or single radio groups, we keep a unique name
        $groupName = uniqid('group_');

        switch ($layout) {
            case 'stacked':
                $html  = $question ? "<div class='checkboxesQuestion' style='margin-bottom:10px;'>{$question}</div>" : "";
                $html .= "<div>";
                foreach ($data as $key => $option) {
                    $isChecked = (strcasecmp($checkedValue, $key) == 0);
                    $checked   = $isChecked ? 'checked' : '';
                    $html     .= <<<HTML
<label class="checkboxContainer" style="display:block;">
  <input type="{$typeAttr}" name="{$groupName}" value="{$key}" id="{$id}" {$customAttrs} {$checked}>
  <span class="checkmark" style="margin-top: 0;"></span>
  <span>{$option}</span>
</label>
HTML;
                }
                $html .= "</div>";
                return $html;

            case 'inlineTop':
                $html  = $question ? "<div class='checkboxesQuestion'>{$question}</div>" : "";
                $html .= "<div>";
                foreach ($data as $key => $option) {
                    $isChecked = (strcasecmp($checkedValue, $key) == 0);
                    $html     .= $renderInput($key, $option, $groupName, $isChecked);
                }
                $html .= "</div>";
                return $html;

            case 'group':
                $html = $question ? "<div class='checkboxesQuestion'>{$question}</div>" : "";
                foreach ($data as $key => $option) {
                    $isChecked = (strcasecmp($checkedValue, $key) == 0);
                    $checked   = $isChecked ? 'checked' : '';
                    $html .= <<<HTML
<div style="display:flex;border: 1px solid #ccc; margin-top:-1px; min-height:35px;padding:2px;">
  <label style="margin-bottom:0; width:100%;" class="checkboxContainer">
    <input type="{$typeAttr}" name="{$groupName}" value="{$key}" id="{$id}" {$customAttrs} {$checked}>
    <span style="top:4px;left:-1px;height:30px;width:30px;" class="checkmark"></span>
    <span>{$option}</span>
  </label>
</div>
HTML;
                }
                return $html;

            case 'onerow':
                $html = "<div>";
                if ($question) {
                    $html .= "<span class='checkboxesQuestion' style='margin-right:10px;'>{$question}</span>";
                }
                $html .= "<span class='{$id} checkboxes' style='float:right'>";
                foreach ($data as $key => $option) {
                    $requiredAttr = '';
                    $k = $key;
                    if (str_starts_with($key, '||')) {
                        // Mark required
                        $requiredAttr = 'required';
                        $k = substr($key, 2); // remove leading '||'
                    }
                    $isChecked = (strcasecmp($checkedValue, $k) == 0);
                    $html     .= $renderInput($k, $option, $groupName, $isChecked, $requiredAttr);
                }
                $html .= "</span></div>";
                return $html;

            case 'list':
                $html = "<ul>";
                foreach ($data as $key => $option) {
                    $isChecked = (strcasecmp($checkedValue, $key) == 0);
                    $checked   = $isChecked ? 'checked' : '';
                    $html     .= <<<HTML
<li class="checkboxContainer" style="display:block;">
  <input type="{$typeAttr}" name="{$groupName}" value="{$key}" id="{$id}" {$customAttrs} {$checked}>
  <span class="checkmark" style="margin-top:0;"></span>
  <span>{$option}</span>
</li>
HTML;
                }
                $html .= "</ul>";
                return $html;

            default:
                // inline
                $html  = "<div class='{$id} checkboxes'>";
                if ($question) {
                    $html = "<span class='checkboxesQuestion' data-id='{$id}' style='margin-right:10px;'>{$question}</span>" . $html;
                }
                foreach ($data as $key => $option) {
                    $isChecked = (strcasecmp($checkedValue, $key) == 0);
                    $html     .= $renderInput($key, $option, $groupName, $isChecked);
                }
                $html .= "</div>";
                return $html;
        }
    }

    /**
     * A specialized Yes/No generator using checkboxes() logic.
     *
     * @param string $question
     * @param string $id
     * @param bool   $textArea
     * @param string $textAreaPlaceholder
     * @param bool   $textAreaHide
     * @param bool   $textAreaReq
     * @param string $customAttrs
     * @param bool   $hideQuestion
     * @param bool   $require
     * @param string $description
     * @return string
     */
    public static function yesNo(
        string $question = '',
        string $id = '',
        bool   $textArea = false,
        string $textAreaPlaceholder = '',
        bool   $textAreaHide = false,
        bool   $textAreaReq = true,
        string $customAttrs = '',
        bool   $hideQuestion = false,
        bool   $require = false,
        string $description = ''
    ): string {
        if (empty($question)) {
            return '<!-- yesNo(): No question set -->';
        }

        $display     = $hideQuestion ? 'display:none;' : '';
        $hiddenClass = $hideQuestion ? ($id . 'Hidden') : '';

        // If $require is true, we force user to choose by marking one option as "||"
        if ($require) {
            $data = [
                '_question'    => $question,
                '_layout'      => 'onerow',
                '_customAttrs' => $customAttrs,
                '||1'          => 'Yes', // Force yes to be required
                '0'            => 'No'
            ];
        } else {
            $data = [
                '_question'    => $question,
                '_layout'      => 'onerow',
                '_customAttrs' => $customAttrs,
                '1'            => 'Yes',
                '0'            => 'No'
            ];
        }

        if (!empty($id)) {
            $data['_id'] = $id;
        }

        $html = "<div class='{$hiddenClass}' style='{$display}'>";
        $html .= self::checkboxes($data);

        if (!empty($description)) {
            $html .= '<p class="yesNoDescription" style="font-size: small; margin-top:0; margin-bottom:0; display:inline-block;">'
                  . $description . '</p>';
        }

        if ($textArea) {
            $html .= self::textarea(
                id:          $id,
                placeholder: $textAreaPlaceholder,
                hide:        $textAreaHide,
                rows:        3,
                required:    $textAreaReq
            );
        }
        $html .= "</div>";

        return $html;
    }

    /**
     * Render a single checkbox item with optional textarea for more info.
     *
     * @param string $question
     * @param string $id
     * @param bool   $textArea
     * @param string $textAreaPlaceholder
     * @param bool   $textAreaHide
     * @param bool   $textAreaReq
     * @param string $customAttrs
     * @param bool   $hideQuestion
     * @param bool   $require
     * @param string $description
     * @param string $label
     * @param string $pos
     * @param string $class
     * @return string
     */
    public static function singleCheckBox(
        string $question = '',
        string $id = '',
        bool   $textArea = false,
        string $textAreaPlaceholder = '',
        bool   $textAreaHide = false,
        bool   $textAreaReq = true,
        string $customAttrs = '',
        bool   $hideQuestion = false,
        bool   $require = false,
        string $description = '',
        string $label = '',
        string $pos = 'l',
        string $class = ''
    ): string {
        $display     = $hideQuestion ? 'display:none;' : '';
        $hiddenClass = $hideQuestion ? ($id . 'Hidden') : '';

        // If it's required, set key => '||1'
        $key = $require ? '||1' : '1';

        $data = [
            '_question'    => $question,
            '_customAttrs' => $customAttrs,
            $key           => $label
        ];

        if (!empty($id)) {
            $data['_id'] = $id;
        }

        $html = "<div class='{$hiddenClass} {$class}' style='{$display}'>";
        // 'm' => multiple checkboxes
        $html .= self::checkboxes($data, 'm');

        if (!empty($description)) {
            $html .= "<p style='display:inline;'>{$description}</p>";
        }

        if ($textArea) {
            $html .= self::textarea(
                id:          $id,
                placeholder: $textAreaPlaceholder,
                hide:        $textAreaHide,
                rows:        3,
                required:    $textAreaReq
            );
            $html .= '<span style="margin-top:25px;"></span>';
        }

        $html .= "</div>";

        return $html;
    }

    /**
     * Basic Bootstrap card.
     *
     * @param string $id
     * @param string $title
     * @param string $subtitle
     * @param string $content
     * @param string $width
     * @param string $class
     * @return string
     */
    public static function card(
        string $id = '',
        string $title = '',
        string $subtitle = '',
        string $content = '',
        string $width = '18rem;',
        string $class = ''
    ): string {
        $titleHtml    = $title    ? "<h5 class='card-title'>{$title}</h5>" : '';
        $subtitleHtml = $subtitle ? "<h6 class='card-subtitle mb-2 text-muted'>{$subtitle}</h6>" : '';

        return <<<HTML
<div id="{$id}" class="card {$class}" style="width: {$width}">
    <div class="card-body">
        {$titleHtml}
        {$subtitleHtml}
        {$content}
    </div>
</div>
HTML;
    }

    /**
     * Horizontal card layout.
     *
     * @param string $id
     * @param string $title
     * @param string $subtitle
     * @param string $content1
     * @param string $content2
     * @param string $maxWidth
     * @param string $class
     * @return string
     */
    public static function cardHorizontal(
        string $id = '',
        string $title = '',
        string $subtitle = '',
        string $content1 = '',
        string $content2 = '',
        string $maxWidth = '18rem;',
        string $class = ''
    ): string {
        $titleHtml    = $title    ? "<h5 class='card-title'>{$title}</h5>" : '';
        $subtitleHtml = $subtitle ? "<h6 class='card-subtitle mb-2 text-muted'>{$subtitle}</h6>" : '';

        return <<<HTML
<div id="{$id}" class="card mb-3 {$class}" style="max-width: {$maxWidth}">
    <div class="row g-0">
        <div class="col-md-4">
            {$content1}
        </div>
        <div class="col-md-8">
            <div class="card-body">
                {$titleHtml}
                {$subtitleHtml}
                {$content2}
            </div>
        </div>
    </div>
</div>
HTML;
    }

    /**
     * Generates a modern login form with optional remember me checkbox and error display.
     *
     * @param string $action         Form action URL
     * @param string $method         Form method (default: post)
     * @param string $error          Optional error message to display
     * @param bool   $rememberMe     Whether to include remember me checkbox
     * @param string $submitText     Text for the submit button
     * @param string $forgotPassLink Optional link for forgot password
     * @return string
     */
    public static function loginForm(
        string $action = '', 
        string $method = 'post',
        string $error = '',
        bool $rememberMe = true,
        string $submitText = 'Sign In',
        string $forgotPassLink = ''
    ): string {
        $errorAlert = '';
        if (!empty($error)) {
            $errorAlert = <<<HTML
            <div class="alert alert-danger mb-3" role="alert">
                {$error}
            </div>
            HTML;
        }
        
        $rememberMeHtml = '';
        if ($rememberMe) {
            $rememberMeHtml = <<<HTML
            <div class="login-remember-me">
                <input class="form-check-input" type="checkbox" id="rememberMe" name="remember">
                <label class="form-check-label" for="rememberMe">Remember me</label>
            </div>
            HTML;
        }
        
        $forgotPasswordHtml = '';
        if (!empty($forgotPassLink)) {
            $forgotPasswordHtml = <<<HTML
            <div class="text-end mb-3">
                <a href="{$forgotPassLink}" class="text-decoration-none">Forgot Password?</a>
            </div>
            HTML;
        }
        
        return <<<HTML
        <form action="{$action}" method="{$method}" class="login-form">
            {$errorAlert}
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                <label for="username">Username</label>
            </div>
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                <label for="password">Password</label>
            </div>
            {$rememberMeHtml}
            {$forgotPasswordHtml}
            <button type="submit" class="btn btn-primary">{$submitText}</button>
        </form>
        HTML;
    }

    /**
     * Generates a complete login page container with header, body, and footer.
     *
     * @param string $title          Title for the login box
     * @param string $content        Content HTML for the login box (typically the login form)
     * @param string $footerText     Optional footer text
     * @param string $icon           Optional icon class (Bootstrap or FontAwesome)
     * @return string
     */
    public static function loginContainer(
        string $title = 'Login',
        string $content = '',
        string $footerText = '',
        string $icon = 'bi bi-person-circle'
    ): string {
        $iconHtml = '';
        if (!empty($icon)) {
            $iconHtml = "<i class=\"{$icon} login-icon\"></i>";
        }
        
        $footerHtml = '';
        if (!empty($footerText)) {
            $footerHtml = <<<HTML
            <div class="login-footer">
                {$footerText}
            </div>
            HTML;
        }
        
        return <<<HTML
        <div class="login-container">
            <div class="login-header">
                {$iconHtml}
                <h3>{$title}</h3>
            </div>
            <div class="login-body">
                {$content}
            </div>
            {$footerHtml}
        </div>
        HTML;
    }
}
