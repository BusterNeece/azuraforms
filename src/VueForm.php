<?php
namespace AzuraForms;

class VueForm extends AbstractForm
{
    public function addField(
        $field_name,
        $type = 'text',
        array $attributes = [],
        $group = null,
        $overwrite = false
    ): string {
        $attributes['v-model'] = $this->_getVueModel($field_name, $group);

        return parent::addField($field_name, $type, $attributes, $group, $overwrite);
    }

    /**
     * @inheritdoc
     */
    public function renderView($show_empty_fields = false): string
    {
        $output = '';

        foreach($this->options['groups'] as $fieldset_id => $fieldset) {
            if (!empty($fieldset['legend'])) {
                $output .= sprintf('<fieldset id="%s" class="%s">',
                    $fieldset_id,
                    $fieldset['class'] ?? ''
                );
                $output .= '<legend>'.$fieldset['legend'].'</legend>';

                if (!empty($fieldset['description'])) {
                    $output .= '<p>'.$fieldset['description'].'</p>';
                }
            }

            $output .= '<dl>';

            foreach($fieldset['elements'] as $element_id => $element_info) {
                if (isset($this->fields[$element_id])) {
                    $field = $this->fields[$element_id];
                    $field->setValue('{{ '.$this->_getVueModel($element_id, $element_info[1]['belongsTo'] ?? null).' }}');

                    $output .= $field->renderView($show_empty_fields);
                }
            }

            $output .= '</dl>';

            if (!empty($fieldset['legend'])) {
                $output .= '</fieldset>';
            }
        }

        return $output;
    }

    /**
     * @inheritdoc
     */
    public function renderHidden(): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function openForm(): string
    {
        $class = 'form';
        if (isset($this->options['class'])) {
            $class .= ' '.$this->options['class'];
        }

        return sprintf('<form id="%s" class="%s">', $this->name, $class);
    }

    /**
     * @param string $field_name
     * @param null $group
     * @return string
     */
    protected function _getVueModel($field_name, $group = null): string
    {
        return (null === $group)
            ? 'form.'.$field_name
            : 'form.'.$group.'.'.$field_name;
    }
}
