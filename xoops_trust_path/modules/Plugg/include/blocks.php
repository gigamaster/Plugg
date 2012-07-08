<?php
$language = empty($GLOBALS['xoopsConfig']['language']) ? 'english' : $GLOBALS['xoopsConfig']['language'];
$lang_dir = dirname(__FILE__) . '/../language/';
if (file_exists($lang_file = $lang_dir . $language . '/blocks.php')) {
    include_once $lang_file;
} else {
    include_once $lang_dir . 'english/blocks.php';
}

if (!function_exists('b_plugg_widget')) {

function b_plugg_widget($options)
{   
    $block = array();
    list($module_dirname, $plugin_name, $widget_name) = $options;
    require dirname(__FILE__) . '/common.php';

    // Show on own plugin pages only?
    if (!empty($options[3]) && SabaiXOOPS::isInModule($module_dirname)) {
        if (($current_plugin = $plugg->getPlugin())
            && $current_plugin->name != $plugin_name
        ) {
            return $block;
        }
    }

    // Render widget and assign result to block content
    if ($plugin = $plugg->getPlugin($plugin_name)) {
        // Modify URL route base
        $prev_url_base = $plugg->getUrlBase();
        $plugg->setUrlBase('/' . $plugin_name);

        // Get widget setting values if any
        $widget_values = array();
        if (($widget_options = array_slice($options, 4))
            && ($widget_settings = $plugin->widgetsGetWidgetSettings($widget_name))
        ) {
            // Map xoops block options to Plugg widget settings
            foreach (array_keys($widget_settings) as $k) {
                if (null === $widget_value = array_shift($widget_options)) break;
                // Multiple values are separated with a comma
                if (is_array(@$widget_settings[$k]['#default_value'])) {
                    $widget_value = !empty($widget_value) ? explode(',', $widget_value) : array();
                }
                $widget_values[$k] = $widget_value;
            }
        }

        // Get widget content
        if (($widget_content = $plugin->widgetsGetWidgetContent($widget_name, $widget_values, $plugg->getUser()))
            && !empty($widget_content['content'])
        ) {
            $view = new Sabai_Application_TemplateView($plugg, 'widget');
            $view->addTemplateDir(dirname(__FILE__));

            $view->widget = array_merge(
                $widget_content,
                array(
                    'plugin' => $plugin->name,
                    'name' => $widget_name,
                )
            );
            
            $block['content'] = $view->render();
        }

        // Set back the route base
        $plugg->setUrlBase($prev_url_base);
    }
    return $block;
}

function b_plugg_widget_edit($options)
{
    list($module_dirname, $plugin_name, $widget_name) = $options;
    require dirname(__FILE__) . '/common.php';

    if (!$plugin = $plugg->getPlugin($plugin_name)) return '';

    // Render hidden elements first otherwise the form renderer will move hidden elements to the bottom of form
    $elements = array(
        'options' => array(
            '#tree' => true,
            0 => array('#type' => 'hidden', '#value' => $module_dirname),
            1 => array('#type' => 'hidden', '#value' => $plugin_name),
            2 => array('#type' => 'hidden', '#value' => $widget_name),
        ),
    );
    $form = $plugg->getPlugin('Form')->renderForm($elements, true);

    $elements = array(
        'options' => array(
            '#tree' => true,
            3 => array(
                '#type' => 'yesno', // checkbox will not work if unchecked
                '#title' => _MB_PLUGG_BLOCKSHOW,
                '#default_value' => !empty($options[3]),
            ),
        ),
    );

    // Get additional settings
    if ($widget_settings = $plugin->widgetsGetWidgetSettings($widget_name)) {
        $index = 3;
        foreach ($widget_settings as $key => $data) {
            $elements['options'][++$index] = array_merge(
                $data,
                array(
                    '#type' => @$data['#type'],
                    '#title' => isset($data['#title']) ? $data['#title'] : '',
                    '#default_value' => isset($options[$index]) ? $options[$index] : null,
                )
            );
        }
    }

    $form .= $plugg->getPlugin('Form')->renderForm($elements, true);

    return $form;
}

}