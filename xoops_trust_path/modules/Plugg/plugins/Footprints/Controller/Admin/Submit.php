<?php
class Plugg_Footprints_Controller_Admin_Submit extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (!$request->isPost()) {
            $response->setError($this->_('Invalid request'));
            return;
        }
        if (!$footprints = $request->asArray('footprints')) {
            $response->setError($this->_('Invalid request'));
            return;
        }
        if (!$token_value = $request->asStr(Plugg::PARAM_TOKEN, false)) {
            $response->setError($this->_('Invalid request'));
            return;
        }
        if (!Sabai_Token::validate($token_value, 'footprints_admin_submit')) {
            $response->setError($this->_('Invalid request'));
            return;
        }
        foreach (array('hide', 'unhide', 'delete') as $action) {
            if ($request->asBool($action, false)) {
                break;
            }
        }
        switch ($action) {
            case 'hide':
                if (false === $num = $this->_hide($request, $footprints)) {
                    $response->setError($this->_('Could not hide selected footprints'));
                } else {
                    $response->setSuccess(sprintf($this->_('%d footprints hidden successfully'), $num));
                }
                break;
            case 'unhide':
                if (false === $num = $this->_unhide($request, $footprints)) {
                    $response->setError($this->_('Could not unhide selected footprints'));
                } else {
                    $response->setSuccess(sprintf($this->_('%d footprints unhidden successfully'), $num));
                }
                break;
            case 'delete':
                if (false === $num = $this->_delete($request, $footprints)) {
                    $response->setError($this->_('Could not delete selected footprints'));
                } else {
                    $response->setSuccess(sprintf($this->_('%d footprints deleted successfully'), $num));
                }
                break;
            default:
                $response->setError($this->_('Invalid request'));
        }
    }

    private function _hide($request, $footprintIds)
    {
        $model = $this->getPluginModel();
        $footprints = $model->Footprint
            ->criteria()
            ->hidden_is(0)
            ->id_in($footprintIds)
            ->fetch();
        foreach ($footprints as $footprint) {
            $footprint->hidden = 1;
        }

        return $model->commit();
    }

    private function _unhide($request, $footprintIds)
    {
        $model = $this->getPluginModel();
        $footprints = $model->Footprint
            ->criteria()
            ->hidden_is(1)
            ->id_in($footprintIds)
            ->fetch();
        foreach ($footprints as $footprint) {
            $footprint->hidden = 0;
        }

        return $model->commit();
    }

    private function _delete($request, $footprintIds)
    {
        $model = $this->getPluginModel();
        $footprints = $model->Footprint
            ->criteria()
            ->id_in($footprintIds)
            ->fetch();
        foreach ($footprints as $footprint) {
            $footprint->markRemoved();
        }

        return $model->commit();
    }
}