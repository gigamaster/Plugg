<?php
class Plugg_Form_Controller_Main_Index extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $vars = array(
            'forms' => $this->getPluginModel()->Form->criteria()->hidden_is(0)
                ->fetch(0, 0, array('weight', 'title'), array('ASC', 'ASC'))
        );
            
        $response->setContent($this->RenderTemplate('form_main_index', $vars));
    }
}