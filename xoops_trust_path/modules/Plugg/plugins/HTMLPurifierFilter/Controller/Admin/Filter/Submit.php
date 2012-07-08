<?php
class Plugg_HTMLPurifierFilter_Admin_Filter_Submit extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (!$request->isPost()) {
            $response->setError($this->_('Invalid request'));
            return;
        }
        if (!$filters = $request->asArray('filters')) {
            $response->setError($this->_('Invalid request'));
            return;
        }
        if (!$token_value = $request->asStr(Plugg::PARAM_TOKEN, false)) {
            $response->setError($this->_('Invalid request'));
            return;
        }
        if (!Sabai_Token::validate($token_value, 'htmlpurifierfilter_admin_submit')) {
            $response->setError($this->_('Invalid request'));
            return;
        }

        $model = $this->getPluginModel();
        $filters_current = $model->Customfilter
            ->criteria()
            ->id_in(array_keys($filters))
            ->fetch();
        foreach ($filters_current as $filter) {
            $filter_id = $filter->id;
            if ($filter->order != $filter_order = intval($filters[$filter_id]['order'])) {
                $filter->order = $filter_order;
            }
            if ($filter->active) {
                if (empty($filters[$filter_id]['active'])) $filter->active = 0;
            } else {
                if (!empty($filters[$filter_id]['active'])) $filter->active = 1;
            }
        }
        if (false === $model->commit()) {
            $response->setError($this->_('An error occurred while updating data.'));
        } else {
            $response->setSuccess($this->_('Data updated successfully.'));
        }
    }
}