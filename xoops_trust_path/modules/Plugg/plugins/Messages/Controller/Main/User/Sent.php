<?php
class Plugg_Messages_Controller_Main_User_Sent extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $model = $this->getPluginModel();

        $criteria = $model->createCriteria('Message')->deleted_is(0)
            ->type_is(Plugg_Messages_Plugin::MESSAGE_TYPE_OUTGOING);

        switch ($messages_select = $request->asStr('messages_select')) {
            case 'read':
                $criteria->read_is(1);
                break;
            case 'unread':
                $criteria->read_is(0);
                break;
            case 'starred':
                $criteria->star_is(1);
                break;
            case 'unstarred':
                $criteria->star_is(0);
                break;
            default:
                $messages_select = 'all';
        }

        $messages_sortby_allowed = array(
            'created,DESC' => $this->_('Newest first'),
            'created,ASC' => $this->_('Oldest first'),
        );
        $messages_sortby = $request->asStr('messages_sortby', 'created,DESC', array_keys($messages_sortby_allowed));
        $sortby = explode(',', $messages_sortby);
        $pages = $model->Message->paginateByUserAndCriteria($this->identity->id, $criteria, 30, $sortby[0], $sortby[1]);
        $page = $pages->getValidPage($request->asInt('p', 1));
        
        $vars = array(
            'messages' => $page->getElements(),
            'messages_pages' => $pages,
            'messages_page' => $page,
            'messages_sortby' => $messages_sortby,
            'messages_sortby_allowed' => $messages_sortby_allowed,
            'messages_select' => $messages_select,
            'delete_older_than_days' => $this->getPlugin()->getConfig('deleteOlderThanDays'),
        );

        $response->setContent($this->RenderTemplate('messages_main_user_sent', $vars));
    }
}