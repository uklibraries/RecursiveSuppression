<?php
/**
 *structured_name RecursiveSuppression
 * 
 * @copyright Copyright 2015 Michael Slone 
 * @license http://opensource.org/licenses/GPL-3.0 GPLv3
 */

/**
 * The RecursiveSuppression plugin.
 * 
 * @package Omeka\Plugins\RecursiveSuppression
 */
class RecursiveSuppressionPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'admin_head',
    );

    protected $_filters = array(
        'element_types_info',
    );

    public function hookAdminHead($args) {
        $request = Zend_Controller_Front::getInstance()->getRequest();

        $module = $request->getModuleName();
        if (is_null($module)) {
            $module = 'default';
        }
        $controller = $request->getControllerName();
        $action = $request->getActionName();

        if ($module === 'default'
            && $controller === 'items'
            && in_array($action, array('add', 'edit')))
        {
            queue_js_file('recursive_suppression');
            queue_css_string("span.dependent-hidden {display: none}");
            queue_css_string("input.suppression {margin: 0; margin-left: 10px; margin-top: 5px; margin-bottom: 10px;}");
            queue_css_string("label.suppression {clear: left; font-weight: bold; float: left; line-height: 1.5em; margin: 0 0 0px 0; min-width: 150px}");
        }
    }

    public function filterElementTypesInfo($types) {
        $types['suppression'] = array(
            'label' => __('Suppression'),
            'filters' => array(
                'ElementInput' => array($this, 'filterElementInput'),
                'Display' => array($this, 'filterDisplay'),
            ),
            'hooks' => array(
            ),
        );
        $types['isuppression'] = array(
            'label' => __('Interview Suppression'),
            'filters' => array(
                'ElementInput' => array($this, 'filterIElementInput'),
                'Display' => array($this, 'filterIDisplay'),
            ),
            'hooks' => array(
            ),
        );
        return $types;
    }

    public function filterElementInput($components, $args) {
        $view = get_view();
        $element = $args['element'];
        $element_id = $element->id;
        $index = $args['index'];
        $name = "Elements[$element_id][$index][text]";
        $id = "Elements-$element_id-$index-text";
        $recursive_id = "$id-recursive";
        $recursive_reason_id = "$id-recursive-reason";
        $description_id = "$id-description";
        $description_reason_id = "$id-description-reason";
        $value = $args['value'];
        if ($value === '') {
            $value = '{"recursive":false,"recursive-reason":"","description":false,"description-reason":""}';
        }
        $m = json_decode($value, true);
        $value = json_encode(array(
            'recursive' => $m['recursive'] ? true : false,
            'recursive-reason' => $m['recursive-reason'],
            'description' => $m['description'] ? true : false,
            'description-reason' => $m['description-reason'],
        ));

        $components['input'] = <<<EOT
<input type="hidden"
       name="{$view->escape($name)}"
       id="{$view->escape($id)}"
       data-type="suppression"
       data-recursive="{$view->escape($recursive_id)}"
       data-recursive-reason="{$view->escape($recursive_reason_id)}"
       data-description="{$view->escape($description_id)}"
       data-description-reason="{$view->escape($description_reason_id)}"
       value="{$view->escape($value)}"/>
EOT;

        $rchecked = '';
        $recursive_reason_visibility = 'dependent-hidden';
        $dchecked = '';
        $description_visibility = 'dependent-hidden';
        $description_reason_visibility = 'dependent-hidden';

        if ($m['recursive']) {
            $recursive_reason_visibility = 'dependent-display';
            $description_visibility = 'dependent-display';
            $rchecked = 'checked="checked"';
        }
        if ($m['description']) {
            $description_reason_visibility = 'dependent-display';
            $dchecked = 'checked="checked"';
        }

        $components['input'] .= <<<EOT
<input name="{$view->escape($recursive_id)}"
       class="suppression"
       id="{$view->escape($recursive_id)}"
       type="checkbox"
       data-parent="{$view->escape($id)}"
       $rchecked>
<label class="suppression" 
       for="{$view->escape($recursive_id)}">{$view->escape(__('Suppress interviews'))} </label>
<span class="recursive-reason {$view->escape($recursive_reason_visibility)}">
<label class="suppression-reason"
       for="{$view->escape($recursive_reason_id)}">{$view->escape(__('Reason for suppression'))} </label>
<textarea name="{$view->escape($recursive_reason_id)}"
       class="suppression-reason"
       id="{$view->escape($recursive_reason_id)}"
       data-parent="{$view->escape($id)}"
       rows="3"
       cols="50">{$view->escape($m['recursive-reason'])}</textarea>
</span>
<span class="description-checkbox {$view->escape($description_visibility)}">
<input name="{$view->escape($description_id)}"
       id="{$view->escape($description_id)}"
       class="suppression"
       type="checkbox"
       data-parent="{$view->escape($id)}"
       $dchecked>
<label class="suppression" 
       for="{$view->escape($description_id)}">{$view->escape(__('Suppress description'))} </label>
</span>
<span class="description-reason {$view->escape($description_reason_visibility)}">
<label class="suppression-reason"
       for="{$view->escape($description_reason_id)}">{$view->escape(__('Reason for suppression'))} </label>
<textarea name="{$view->escape($description_reason_id)}"
       class="suppression-reason"
       id="{$view->escape($description_reason_id)}"
       data-parent="{$view->escape($id)}"
       rows="3"
       cols="50">{$view->escape($m['description-reason'])}</textarea>
</span>
EOT;

        $components['html_checkbox'] = NULL;
        return $components;
    }

    public function filterIElementInput($components, $args) {
        $view = get_view();
        $element = $args['element'];
        $element_id = $element->id;
        $index = $args['index'];
        $name = "Elements[$element_id][$index][text]";
        $id = "Elements-$element_id-$index-text";
        $description_id = "$id-description";
        $description_reason_id = "$id-description-reason";
        $value = $args['value'];
        if ($value === '') {
            $value = '{"description":false,"description-reason":""}';
        }
        $m = json_decode($value, true);
        $value = json_encode(array(
            'description' => $m['description'] ? true : false,
            'description-reason' => $m['description-reason'],
        ));

        $components['input'] = <<<EOT
<input type="hidden"
       name="{$view->escape($name)}"
       id="{$view->escape($id)}"
       data-type="isuppression"
       data-description="{$view->escape($description_id)}"
       data-description-reason="{$view->escape($description_reason_id)}"
       value="{$view->escape($value)}"/>
EOT;

        $dchecked = '';
        $description_visibity = 'dependent-hidden';
        $description_reason_visibility = 'dependent-hidden';

        if ($m['description']) {
            $description_reason_visibility = 'dependent-display';
            $dchecked = 'checked="checked"';
        }

        $components['input'] .= <<<EOT
<input name="{$view->escape($description_id)}"
       id="{$view->escape($description_id)}"
       class="suppression"
       type="checkbox"
       data-parent="{$view->escape($id)}"
       $dchecked>
<label class="suppression" 
       for="{$view->escape($description_id)}">{$view->escape(__('Suppress description'))} </label>
<span class="description-reason {$view->escape($description_reason_visibility)}">
<label class="suppression-reason"
       for="{$view->escape($description_reason_id)}">{$view->escape(__('Reason for suppression'))} </label>
<textarea name="{$view->escape($description_reason_id)}"
       class="suppression-reason"
       id="{$view->escape($description_reason_id)}"
       data-parent="{$view->escape($id)}"
       rows="3"
       cols="50">{$view->escape($m['description-reason'])}</textarea>
</span>
EOT;

        $components['html_checkbox'] = NULL;
        return $components;
    }

    public function filterDisplay($text, $args) {
        $view = get_view();
        $text = str_replace('&quot;', '"', $text);
        $m = json_decode($text, true);
        $pieces = array();

        $rsuppressed = 'No';
        $rreason = '';
        if ($m['recursive']) {
            $rsuppressed = 'Yes';
            $rreason = "<b>(Reason: {$view->escape($m['recursive-reason'])})</b>";
        }

        $dsuppressed = 'No';
        $dreason = '';
        if ($m['description']) {
            $dsuppressed = 'Yes';
            $dreason = "<b>(Reason: {$view->escape($m['description-reason'])})</b>";
        }

        return <<<EOT
<p>
  Interviews suppressed: <b>$rsuppressed</b>. $rreason
</p>

<p>
  Description suppressed: <b>$dsuppressed</b>. $dreason
</p>
EOT;
    }

    public function filterIDisplay($text, $args) {
        $view = get_view();
        $text = str_replace('&quot;', '"', $text);
        $m = json_decode($text, true);
        $pieces = array();

        $dsuppressed = 'No';
        $dreason = '';
        if ($m['description']) {
            $dsuppressed = 'Yes';
            $dreason = "<b>(Reason: {$view->escape($m['description-reason'])})</b>";
        }

        return <<<EOT
<p>
  Description suppressed: <b>$dsuppressed</b>. $dreason
</p>
EOT;
    }
}
