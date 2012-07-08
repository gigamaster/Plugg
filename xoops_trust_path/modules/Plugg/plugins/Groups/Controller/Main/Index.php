<?php
class Plugg_Groups_Controller_Main_Index extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $model = $this->getPluginModel();
        $vars = array(
            'pages' => $pages = $model->Group->criteria()->status_is(Plugg_Groups_Plugin::GROUP_STATUS_APPROVED)->paginate(20, 'created', 'DESC'),
            'page' => $page = $pages->getValidPage($request->asInt('p', 1)),
            'groups' => $page->getElements()
        );
        
        $groups_i_belong = array();
        if ($vars['groups']->count()) {
            $memberships = $model->Member->criteria()
                ->groupId_in($vars['groups']->getAllIds())
                ->userId_is($this->getUser()->id)
                ->fetch();
            foreach ($memberships as $membership) {
                $groups_i_belong[$membership->group_id] = $membership;
            }
        }
        $vars['groups_i_belong'] = $groups_i_belong;
        
        $response->setContent($this->RenderTemplate('groups_main_index', $vars));
    }
}