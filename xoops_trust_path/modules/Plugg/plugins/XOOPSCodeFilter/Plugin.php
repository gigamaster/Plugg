<?php
class Plugg_XOOPSCodeFilter_Plugin extends Plugg_Plugin implements Plugg_Filters_Filter
{
    public function filtersFilterGetNames()
    {
        return array('default' => $this->_('XOOPS Code'));
    }

    public function filtersFilterGetNicename($filterName)
    {
        return $this->_('XOOPS Code');
    }

    public function filtersFilterGetSummary($filterName)
    {
        return $this->_('Allows editing text using the XOOPS syntax.');
    }

    public function filtersFilterText($filterName, $text)
    {
        // Convert quoted text into XOOPS Code
        $text = $this->_application->getPlugin('Filters')->filterQuotedText($text, false, '[quote]', '[/quote]');

        $html = $this->getConfig('allowHTMLTags');
        $smiley = $this->getConfig('allowSmilies');
        $xoopscode = 1;
        $image = $this->getConfig('allowXOOPSCodeImgTag');
        $nl2br = 1;
        return MyTextSanitizer::getInstance()->displayTarea($text, $html, $smiley, $xoopscode, $image, $nl2br);
    }

    public function filtersFilterGetTips($filterName, $long)
    {
        return array(
            $this->_('XOOPS Code is <strong>On</strong>'),
            $this->getConfig('allowSmilies') ? $this->_('Smilies are <strong>On</strong>') : $this->_('Smilies are <strong>Off</strong>'),
            $this->getConfig('allowXOOPSCodeImgTag') ? $this->_('[img] code is <strong>On</strong>') : $this->_('[img] code is <strong>Off</strong>'),
            $this->getConfig('allowHTMLTags') ? $this->_('HTML tags are <strong>On</strong>') : $this->_('HTML tags are <strong>Off</strong>'),
        );
    }

    public function filtersFilterGetSettings($filterName)
    {
        return array(
            'allowSmilies' => array(
                '#title' => $this->_('Enable smilies.'),
                '#description' => $this->_('Check this option to allow smilies to be used in text.'),
                '#required' => true,
                '#type' => 'checkbox',
                '#default_value' => $this->getConfig('allowSmilies'),
            ),
            'allowXOOPSCodeImgTag' => array(
                '#title' => $this->_('Enable XOOPS Code [img] tag.'),
                '#description' => $this->_('Check this option to allow XOOPS Code [img] tag to be used in text.'),
                '#required' => true,
                '#type' => 'checkbox',
                '#default_value' => $this->getConfig('allowXOOPSCodeImgTag'),
            ),
            'allowHTMLTags' => array(
                '#title' => $this->_('Allow HTML tags.'),
                '#description' => $this->_('Check this option to allow HTML tags to be used in text. For security reasons it is strongly recommended that you disable HTML tags if you are unsure.'),
                '#default_value' => $this->getConfig('allowHTMLTags'),
                '#required' => true,
                '#type' => 'checkbox'
            ),
        );
    }
    
    public function getDefaultConfig()
    {
        return array(
            'allowSmilies' => true,
            'allowXOOPSCodeImgTag' => false,
            'allowHTMLTags' => false
        );
    }
}