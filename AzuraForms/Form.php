<?php
/**
 * Nibble Forms 2 library
 * Copyright (c) 2013 Luke Rotherfield, Nibble Development
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace AzuraForms;

class Form
{
    protected $action, $method, $submit_value, $fields, $sticky, $format, $message_type, $multiple_errors, $html5;
    protected $valid = true;
    protected $name = 'azuraforms_form';
    protected $messages = array();
    protected $data = array();
    protected $formats = array(
        'list' => array(
            'open_form' => '<ul>',
            'close_form' => '</ul>',
            'open_form_body' => '',
            'close_form_body' => '',
            'open_field' => '',
            'close_field' => '',
            'open_html' => "<li>\n",
            'close_html' => "</li>\n",
            'open_submit' => "<li>\n",
            'close_submit' => "</li>\n"
        ),
        'table' => array(
            'open_form' => '<table>',
            'close_form' => '</table>',
            'open_form_body' => '<tbody>',
            'close_form_body' => '</tbody>',
            'open_field' => "<tr>\n",
            'close_field' => "</tr>\n",
            'open_html' => "<td>\n",
            'close_html' => "</td>\n",
            'open_submit' => '<tfoot><tr><td>',
            'close_submit' => '</td></tr></tfoot>'
        )
    );
    protected $filters;
    protected $validators;

    /**
     * @param string $action
     * @param string $submit_value
     * @param bool $html5
     * @param string $method
     * @param bool $sticky
     * @param string $message_type
     * @param string $format
     * @param bool|string $multiple_errors
     */
    public function __construct(
        $action = '',
        $submit_value = 'Submit',
        $html5 = true,
        $method = 'post',
        $sticky = true,
        $message_type = 'list',
        $format = 'list',
        $multiple_errors = false
    ) {
        $this->action = $action;
        $this->submit_value = $submit_value;
        $this->html5 = $html5;
        $this->method = $method;
        $this->sticky = $sticky;
        $this->message_type = $message_type;
        $this->format = $format;
        $this->multiple_errors = $multiple_errors;

        $this->fields = new \ArrayObject([], \ArrayObject::ARRAY_AS_PROPS);
        $this->filters = [];
        $this->validators = [];
    }

    /**
     * Add a field to the form instance
     *
     * @param string $field_name
     * @param string $type
     * @param array $attributes
     * @param boolean $overwrite
     *
     * @return boolean
     */
    public function addField($field_name, $type = 'text', array $attributes = array(), $overwrite = false)
    {
        $namespace_options = [
            "\\AzuraForms\\Field\\" . ucfirst($type),
        ];

        foreach ($namespace_options as $namespace_option) {
            if (class_exists($namespace_option)) {
                $namespace = $namespace_option;
                break;
            }
        }

        if (!isset($namespace)) {
            return false;
        }

        if (isset($attributes['label'])) {
            $label = $attributes['label'];
        } else {
            $label = ucfirst(str_replace('_', ' ', $field_name));
        }

        $field_name = Useful::slugify($field_name, '_');

        if (isset($this->fields->$field_name) && !$overwrite) {
            return false;
        }

        if (!empty($attributes['filter'])) {
            $this->filters[$field_name] = $attributes['filter'];
            unset($attributes['filter']);
        }
        if (!empty($attributes['validator'])) {
            $this->validators[$field_name] = $attributes['validator'];
            unset($attributes['validator']);
        }

        $this->fields->$field_name = new $namespace($label, $attributes);
        $this->fields->$field_name->setForm($this);

        return $this->fields->$field_name;
    }

    /**
     * Retrieve an already added field.
     *
     * @param $key
     * @return mixed
     */
    public function getField($key)
    {
        return $this->fields->$key;
    }

    /**
     * Set the name of the form
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get form name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get form method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Add data to populate the form
     *
     * @param array $data
     */
    public function addData(array $data)
    {
        $this->data = array_merge($this->data, $data);
    }

    /**
     * Validate the submitted form
     *
     * @return boolean
     */
    public function validate(array $request = null)
    {
        if ($request === null) {
            $request = strtoupper($this->method) === 'POST' ? (array)$_POST : (array)$_GET;
        }

        if (empty($request)) {
            $this->valid = false;
            return false;
        }

        $this->data = $request;
        $form_data = $request;

        // Check CSRF token.
        if ((isset($_SESSION["nibble_forms"]["_crsf_token"][$this->name])
                && $form_data["_crsf_token"] !== $_SESSION["nibble_forms"]["_crsf_token"][$this->name])
            || !isset($_SESSION["nibble_forms"]["_crsf_token"], $form_data["_crsf_token"])
        ) {
            $title = str_replace("_", ' ', ucfirst('CRSF error'));
            if ($this->message_type === 'list') {
                $this->messages[] = array('title' => $title, 'message' => ucfirst('CRSF token invalid'));
            }

            $this->valid = false;
        }

        $_SESSION["nibble_forms"]["_crsf_token"] = array();

        // Retrieve file data.
        $file_data = $this->_fixFilesArray($_FILES ?? array());

        // Validate individual fields using the class validator.
        foreach ($this->fields as $key => $value) {
            /** @var Field\AbstractField $value */
            if (!$value->validate($form_data[$key] ?? $file_data[$key] ?? '')) {
                $this->valid = false;
                return false;
            }

            // Apply additional validators if specified.
            if (isset($this->validators[$key])) {
                /** @var callable $validator */
                $validator = $this->validators[$key];

                if (!$validator($form_data[$key] ?? $file_data[$key] ?? '', $value)) {
                    $this->valid = false;
                    return false;
                }
            }
        }

        return $this->valid;
    }

    /**
     * Fixes the odd indexing of multiple file uploads from the format:
     *
     * $_FILES['field']['key']['index']
     *
     * To the more standard and appropriate:
     *
     * $_FILES['field']['index']['key']
     *
     * @param array $files
     * @return array
     * @author Corey Ballou
     * @link http://www.jqueryin.com
     */
    protected function _fixFilesArray($files)
    {
        $names = array('name' => 1, 'type' => 1, 'tmp_name' => 1, 'error' => 1, 'size' => 1);

        foreach ($files as $key => $part) {
            // only deal with valid keys and multiple files
            $key = (string) $key;
            if (isset($names[$key]) && is_array($part)) {
                foreach ($part as $position => $value) {
                    $files[$position][$key] = $value;
                }
                // remove old key reference
                unset($files[$key]);
            }
        }

        return $files;
    }

    /**
     * Return the stored data for a field, running any filters if necessary.
     *
     * @param $key
     * @param bool $raw     Apply filters to the returned data.
     * @return bool|mixed
     */
    public function getData($key, $raw = false)
    {
        if (!$raw && isset($this->filters[$key]) && is_callable($this->filters[$key])) {
            return $this->filters[$key]($this->data[$key] ?? false);
        }

        return $this->data[$key] ?? false;
    }

    /**
     * Render the entire form including submit button, errors, form tags etc
     *
     * @return string
     */
    public function render()
    {
        $fields = '';
        $error = $this->valid ? ''
            : '<p class="error">Sorry there were some errors in the form, problem fields have been highlighted</p>';
        $format = (object)$this->formats[$this->format];
        $this->setToken();

        foreach ($this->fields as $key => $value) {
            /** @var Field\AbstractField $value */

            $format = (object)$this->formats[$this->format];
            $temp = isset($this->data[$key]) ? $value->returnField($this->name, $key, $this->data[$key])
                : $value->returnField($this->name, $key);
            $fields .= $format->open_field;
            if ($temp['label']) {
                $fields .= $format->open_html . $temp['label'] . $format->close_html;
            }
            if (isset($temp['messages'])) {
                foreach ($temp['messages'] as $message) {
                    if ($this->message_type === 'inline') {
                        $fields .= "$format->open_html <p class=\"error\">$message</p> $format->close_html";
                    } else {
                        $this->setMessages($message, $key);
                    }
                    if (!$this->multiple_errors) {
                        break;
                    }
                }
            }
            $fields .= $format->open_html . $temp['field'] . $format->close_html . $format->close_field;
        }

        if (!empty($this->messages)) {
            $this->buildMessages();
        } else {
            $this->messages = false;
        }

        $attributes = $this->getFormAttributes();

        return <<<FORM
            $error
            $this->messages
            <form class="form" action="$this->action" method="$this->method" {$attributes['enctype']} {$attributes['html5']}>
              $format->open_form
                $format->open_form_body
                  $fields
                $format->close_form_body
                $format->open_submit
                  <input type="submit" name="submit" value="$this->submit_value" />
                $format->close_submit
              $format->close_form
            </form>
FORM;
    }

    /**
     * Returns the HTML for a specific form field ususally in the form of input tags
     *
     * @param string $name
     *
     * @return string
     */
    public function renderField($name)
    {
        return $this->getFieldData($name, 'field');
    }

    /**
     * Returns the HTML for a specific form field's label
     *
     * @param string $name
     *
     * @return string
     */
    public function renderLabel($name)
    {
        return $this->getFieldData($name, 'label');
    }

    /**
     * Returns the error string for a specific form field
     *
     * @param string $name
     *
     * @return string
     */
    public function renderError($name)
    {
        $error_string = '';
        if (!is_array($this->getFieldData($name, 'messages'))) {
            return false;
        }
        foreach ($this->getFieldData($name, 'messages') as $error) {
            $error_string .= "<li>$error</li>";
        }

        return $error_string === '' ? false : "<ul>$error_string</ul>";
    }

    /**
     * Returns the boolean depending on existance of errors for specified
     * form field
     *
     * @param string $name
     *
     * @return boolean
     */
    public function hasError($name)
    {
        $errors = $this->getFieldData($name, 'messages');
        return !(!$errors || !is_array($errors));
    }

    /**
     * Returns the entire HTML structure for a form field
     *
     * @param string $name
     *
     * @return string
     */
    public function renderRow($name)
    {
        $row_string = $this->renderError($name);
        $row_string .= $this->renderLabel($name);
        $row_string .= $this->renderField($name);

        return $row_string;
    }

    /**
     * Returns HTML for all hidden fields including crsf protection
     *
     * @return string
     */
    public function renderHidden()
    {
        $this->setToken();
        $fields = array();

        foreach ($this->fields as $name => $field) {
            if ($field instanceof Field\Hidden) {
                if (isset($this->data[$name])) {
                    $field_data = $field->returnField($this->name, $name, $this->data[$name]);
                } else {
                    $field_data = $field->returnField($this->name, $name);
                }
                $fields[] = $field_data['field'];
            }
        }

        return implode("\n", $fields);
    }

    /**
     * Returns HTML string for all errors in the form
     *
     * @return string
     */
    public function renderErrors()
    {
        $error_string = '';
        foreach ($this->fields as $name => $field) {
            foreach ($this->getFieldData($name, 'messages') as $error) {
                $error_string .= "<li>$error</li>\n";
            }
        }

        return $error_string === '' ? false : "<ul>$error_string</ul>";
    }

    /**
     * Returns the HTML string for opening a form with the correct enctype, action and method
     *
     * @return string
     */
    public function openForm()
    {
        $attributes = $this->getFormAttributes();

        return "<form class=\"form\" action=\"$this->action\" method=\"$this->method\" {$attributes['enctype']} {$attributes['html5']}>";
    }

    /**
     * Return close form tag
     *
     * @return string
     */
    public function closeForm()
    {
        return "</form>";
    }

    /**
     * Check if a field exists
     *
     * @param string $field
     *
     * @return boolean
     */
    public function checkField($field)
    {
        return isset($this->fields->$field);
    }

    /**
     * Get the attributes for the form tag
     *
     * @return array
     */
    protected function getFormAttributes()
    {
        $enctype = '';
        foreach ($this->fields as $field) {
            if ($field instanceof Field\File) {
                $enctype = 'enctype="multipart/form-data"';
            }
        }
        $html5 = $this->html5 ? '' : 'novalidate';

        return array(
            'enctype' => $enctype,
            'html5' => $html5
        );
    }

    /**
     * Adds a message string to the class messages array
     *
     * @param string $message
     * @param string $title
     */
    protected function setMessages($message, $title)
    {
        $title = str_replace("_", ' ', ucfirst($title));
        if ($this->message_type === 'list') {
            $this->messages[] = array('title' => $title, 'message' => ucfirst($message));
        }
    }

    /**
     * Sets the messages array as an HTML string
     */
    protected function buildMessages()
    {
        $messages = '<ul class="error">';
        foreach ($this->messages as $message_array) {
            $messages .= sprintf(
                '<li>%s: %s</li>%s',
                ucfirst(str_replace("_", ' ', $message_array['title'])),
                ucfirst($message_array['message']),
                "\n"
            );
        }
        $this->messages = $messages . '</ul>';
    }

    /**
     * Gets a specific field HTML string from the field class
     *
     * @param string $name
     * @param string $key
     *
     * @return string
     */
    protected function getFieldData($name, $key)
    {
        if (!$this->checkField($name)) {
            return false;
        }

        /** @var Field\AbstractField $field */
        $field = $this->fields->$name;

        if (isset($this->data[$name])) {
            $field = $field->returnField($this->name, $name, $this->data[$name]);
        } else {
            $field = $field->returnField($this->name, $name);
        }

        return $field[$key];
    }

    /**
     * Creates a new CRSF token
     *
     * @return string
     */
    protected function setToken()
    {
        if (!isset($_SESSION["nibble_forms"])) {
            $_SESSION["nibble_forms"] = array();
        }
        if (!isset($_SESSION["nibble_forms"]["_crsf_token"])) {
            $_SESSION["nibble_forms"]["_crsf_token"] = array();
        }
        $_SESSION["nibble_forms"]["_crsf_token"][$this->name] = Useful::randomString(20);

        $this->addField("_crsf_token", "hidden");
        $this->addData(array("_crsf_token" => $_SESSION["nibble_forms"]["_crsf_token"][$this->name]));
    }

}

