<?php

/**
 * Forms generation API.
 *
 * Based on Drupal Forms API, after all it was not that bad. ;)
 */
class Form {

  /**
   * Add a field to the form.
   */
  public function add($field, $weight, $options,$id = NULL) {
    if (!isset($id)) {
      $this->form[$weight][$field] = $options;
    }
    else {
      $options['field'] = $field;
      $this->form[$weight][$id] = $options;
    }
  }

  /**
   * Set form properties.
   */
  public function prop($properties) {
    $this->prop = $properties;
  }

  /**
   * Render and output the form HTML code.
   */
  public function render() {
    $output = '';

    /**
     * Get each field. Print them in order, based on 'weight'.
     */
    ksort($this->form);
    foreach ($this->form as $group) {
      foreach ($group as $field => $options) {
        $fields_output .= $this->render_field($field, $options);
      }
    }

    /**
     * Set the propierties for the form.
     */
    $enctype = ($this->prop['enctype'] ? ' enctype='.$this->prop['enctype'] : '');
    $method  = ($this->prop['method'] ? $this->prop['method'] : 'POST');
    $output .= '<form action="'.$this->prop['path'].'" method="'.$method.'" id="'.$this->prop['formid'].'"'.$enctype.' class="form">';
    if ($this->prop['title']) {
      page_set_title($this->prop['title']);
    }
    if ($this->prop['desc']) {
      $output .= '<p class="text bold">'.$this->prop['desc'].'</p>';
    }
    if ($this->prop['mandatory_msg']) {
      $output .= '<p class="text marginbot15">'.t('Fields marked as <span class="required">*</span> are compulsory.').'</p>';
    }
    $output .= $fields_output;
    $output .= '</form>';

    return $output;
  }

  /**
   * Render a $form item.
   */
  private function render_field($field, $options) {
    $output = '';
    if ($options['pre']) {
      $output .= $options['pre'].NL;
    }
    switch ($options['type']) {

      case 'title':
        $output .= '<h2>'.$options['title'].'</h2>'.NL;
        if ($options['desc']) {
          $output .= '<p class="text">'. $options['desc'] .'</p>'.NL;
        }
        break;

      case 'dummy':
        $output .= $options['desc'].NL;
        break;

      case 'fixed':
        $output .= '<label for="'.$field.'" class="'.$options['style'].'">'.NL;
        $output .= '<span>';
        $output .= $options['title'].'</span>'.NL;
        $output .= $options['value'].NL;
        $output .= '</label>'.NL;
        $output .= $options['desc'] ? '<p class="description">'.$options['desc'].'</p>'.NL : '';
        break;

      case 'text':
      case 'password':
        $label_class = ($options['error'] ? 'fail' : $options['style']);
        $output .= '<label for="'.$field.'" class="'.$label_class.'">'.NL;
        $output .= '<span>';

        $output .= '<p class="form">';
        $output .= $options['title'];
        $output .= $options['required'] ? '<span class="required" title="'.t('This field is required').'">*</span>' : '';
        $output .= '</p>';
        $output .= '</span>'.NL;

        $lenght  = $options['lenght'] > 0 ? $options['lenght'] : 255;
        $value   = $options['value'] ? $options['value'] : '';
        $output .= '<input type="'.$options['type'].'" maxlength="'.$lenght.'" size="40" name="'.$field.'" id="'.$field.'" value="'.$value.'" />'.NL;
        $output .= '</label>'.NL;
        $output .= $options['error'] ? '<p class="fail">'.$options['error'].'</p>'.NL : '';
        $output .= $options['desc']  ? '<p class="description">'.$options['desc'].'</p>'.NL : '';
        break;

      case 'hidden':
        $output .= '<input type="'.$options['type'].'" name="'.$field.'" id="'.$field.'" value="'.$options['value'].'" />'.NL;
        break;

      case 'select':
        $label_class = ($options['error'] ? 'fail' : $options['style']);
        $output .= '<label for="'.$field.'" class="'.$label_class.'">'.NL;

        $output .= '<p class="form">';
        $output .= '<span>';
        $output .= $options['required'] ? '<span class="required" title="'.t('This field is required').'">*</span>' : '';
        $output .= $options['title'];
        $output .= '</p>';
        $output .= '</span>'.NL;

        $output .= '<select name="'.$field.'" id="'.$field.'">';
        if (is_array($options['options'])) {
          foreach ($options['options'] as $value => $option) {
            $output .= '<option value="'.$value.'"';
            if ($options['value'] == $value) {
              $output .= ' selected';
            }
            $output .='>'.$option.'</option>';
          }
        }
        $output .= '</select>';
        $output .= '</label>'.NL;
        break;

      case 'date':
        $opts_class  = ($options['style'] ? ' '.$options['style'] : '');
        $label_class = ($options['error'] ? ' fail' : $opts_class);
        $output .= '<label for="'.$field.'" class="date'.$label_class.'">'.NL;
        $output .= '<span>';
        $output .= $options['required'] ? '<span class="required" title="'.t('This field is required').'">*</span>' : '';
        $output .= $options['title'] .'</span>'.NL;
        $output .= '<select name="'.$field.'[day]" id="'.$field.'-day">';
        for ($i = 1; $i <= 31; $i++) {
          $output .= '<option value="'.$i.'"';
          if ($options['value']['mday'] == $i) {
            $output .= ' selected';
          }
          $output .='>'.$i.'</option>';
        }
        $output .= '</select>'.NL;
        $output .= '<select name="'.$field.'[month]" id="'.$field.'-month">';
        for ($i = 1; $i <= 12; $i++) {
          $output .= '<option value="'.$i.'"';
          if ($options['value']['mon'] == $i) {
            $output .= ' selected';
          }
          $output .='>'.$i.'</option>';
        }
        $output .= '</select>'.NL;
        $output .= '<select name="'.$field.'[year]" id="'.$field.'-year">';
        for ($i = 1900; $i <= date('Y'); $i++) {
          $output .= '<option value="'.$i.'"';
          if ($options['value']['year'] == $i) {
            $output .= ' selected';
          }
          $output .='>'.$i.'</option>';
        }
        $output .= '</select>'.NL;
        $output .= '</label>'.NL;
        break;

      case 'radio':
        $output .= '<div class="radio_box">';
        $output .= '<label><span>';
        $output .= $options['required'] ? '<span class="required" title="'.t('This field is required').'">*</span>' : '';
        $output .= $options['title'] .'</span>'.NL;
        $output .= '</label>';
        if (is_array($options['options'])) {
          $output .= '<div>';
          $i = 1;
          foreach ($options['options'] as $value => $option) {
            $default = '';
            if (isset($options['value'])) {
              $default = ($value == $options['value'] ? ' checked' : '');
            }
            $output .= '<label class="option"><input type="radio" name="'.$field.'" value="'. $value .'"'.$default.'/> '. $option .'</label>';
            $i++;
          }
          $output .= '</div>';
        }
        $output .= $options['error'] ? '<p class="fail">'.$options['error'].'</p>'.NL : '';
        $output .= $options['desc'] ? '<p class="description">'.$options['desc'].'</p>'.NL : '';
        $output .= '</div>';
        break;

      case 'textarea':
        $label_class = ($options['error'] ? 'fail' : $options['style']);
        $output .= '<label for="'.$field.'" class="'.$label_class.'">'.NL;

        $output .= '<p class="form">';
        $output .= '<span>';
        $output .= $options['title'];
        $output .= $options['required'] ? '<span class="required" title="'.t('This field is required').'">*</span>' : '';
        $output .= '</p>';
        $output .= '</span>'.NL;

        $rows    = $options['rows'] > 0 ? $options['rows'] : 5;
        $legal   = $options['legal'] ? ' class="legal"' : '';
        $output .= '<textarea cols="'. $options['cols'] .'" rows="'. $options['rows'] .'" name="'. $field .'" id="'. $field . $legal .'">';
        $output .= $options['value'] ? $options['value'] : '';
        $output .= '</textarea></label>';
        $output .= $options['error'] ? '<p class="fail">'.$options['error'].'</p>'.NL : '';
        $output .= $options['desc'] ? '<p class="description">'.$options['desc'].'</p>'.NL : '';
        break;

      case 'checkbox':
        if (!isset($options['field'])) {
          $label_class = ($options['error'] ? 'fail' : $options['style']);
          $output .= '<div class="checkbox_box"><label class="'.$label_class.'">';
          $checked = $options['checked'] ? ' checked="checked"' : '';
          $output .= '<input type="checkbox" name="'. $field .'" id="'. $field .'" value="1"'.$checked.' ';
          if (isset($options['disabled']) && $options['disabled'] != ''){
            $output .= 'disabled='.$options['disabled'];
          }
          $output .= ' />'.NL;
          $output .= $options['text'].NL;
          $output .= '</label></div>';
        }
        else {
          $label_class = ($options['error'] ? 'fail' : $options['style']);
          $output .= '<div class="checkbox_box"><label class="'.$label_class.'">';
          $checked = $options['checked'] ? ' checked="checked"' : '';
          $output .= '<input type="checkbox" name="'. $options['field'] .'" id="'. $field .'" value="';
          $output .= (isset($options['value'])) ? $options['value'] : '1';
          $output .= '"'.$checked.' />'.NL;
          $output .= $options['text'].NL;
          $output .= '</label></div>';
        }
        break;

      case 'file':
        $this->prop['enctype'] = $options['enctype'];
        $output .= '<label for="'.$field.'">'.NL;
        $output .= '<span>';
        $output .= $options['title'] .'</span>'.NL;
        $size    = $options['size'] ? $options['size'] : 30;
        $output .= '<input type="file" name="'.$field.'" size="'.$size.'" />';
        $output .= '</label>'.NL;
        break;

      case 'submit':
        $button_class = 'btn';
        if (mb_strlen($options['title']) > 15){
          $button_class = 'btn_big';
        }

        $output .= '<button class="'.$button_class.'" name="op" id="'. $field .'" type="submit" onclick="'.$options['onclick'].'"';
        if ($options['accesskey'] != '') {
          $output .= 'accesskey ="'.$options['accesskey'].'"';
        }
        $output .= '>';
        $output .= '<span>'.$options['title'].'</span></button>';
        $output .= $options['desc'] ? '<p class="description '.$options['style'].'" >'.$options['desc'].'</p>'.NL : '';
        break;

      case 'button':
        $output .= '<button type="button" class="btn" id="'.$field.'" onclick="'.$options['onclick'].'" ';
        if ($options['accesskey'] != '') {
          $output .= 'accesskey ="' . $options['accesskey'] . '"';
        }
        $output .= '>';
        $output .= '<span>'.$options['title'].'</span></button>';
        $output .= $options['desc'] ? '<p class="description '.$options['style'].'" >'.$options['desc'].'</p>'.NL : '';

      default:
        break;
    }

    if ($options['post']) {
      $output .= $options['post'].NL;
    }
    return $output;
  }

  // Vars to manage the form we are building.
  private $form = array();
  private $prop = array();
}
