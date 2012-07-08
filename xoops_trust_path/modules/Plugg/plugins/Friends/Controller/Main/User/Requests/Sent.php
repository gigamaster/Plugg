<?php
class Plugg_Friends_Controller_Main_User_Requests_Sent extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $form = array(
            'requests' => array(
                '#type' => 'tableselect',
                '#header' => array('user' => '', 'username' => $this->_('User')),
                '#multiple' => true,
                '#js_select' => true,
                '#options' => array(),
            )
        );
        
        $sortable_headers = array(
            'created' => $this->_('Sent on'),
        );
        $sort = $request->asStr('sort', 'created', array_keys($sortable_headers));
        $order = $request->asStr('order', 'ASC', array('ASC', 'DESC'));
        $path = '/user/' . $this->identity->id . '/friends/requests/sent';
        
        // Add headers
        foreach ($sortable_headers as $header_name => $header_label) {
            $attr = array('title' => sprintf($this->_('Sort by %s'), $header_label));    
            if ($sort === $header_name) {
                $url_params = array('sort' => $header_name, 'order' => $order == 'ASC' ? 'DESC' : 'ASC');
                $attr['class'] = 'plugg-' . strtolower($order);
            } else {
                $url_params = array('sort' => $header_name, 'order' => 'ASC');
            }
            $form['requests']['#header'][$header_name] = $this->LinkToRemote(
                $header_label, 'plugg-friends-requests-sent', $this->getUrl($path, $url_params), array(), array(), $attr
            );
        }
        $form['requests']['#header']['message'] = $this->_('Message');
        $form['requests']['#header']['status'] = $this->_('Status');
        
        // Add rows (options)
        $pages = $this->getPluginModel()->Request->criteria()->userId_is($this->identity->id)
            ->paginate(20, $sort, $order);
        $friend_requests = $pages->getValidPage($request->asInt('p', 1))->getElements();
        foreach ($friend_requests->with('ToUser') as $friend_request) {
            $form['requests']['#options'][$friend_request->id] = array(
                'user' => $this->User_IdentityIcon($friend_request->ToUser),
                'username' => $this->User_IdentityLink($friend_request->ToUser),
                'created' => $this->DateTime($friend_request->created),
                'message' => h($friend_request->message),
                'status' => $friend_request->isPending() ? $this->_('Pending') : ($friend_request->isRejected() ? $this->_('Rejected') : ''),
            );
        }
        
        $this->_submitButtonLabel = $this->_('Cancel pending requests');
        $this->_cancelUrl = null;
        $this->_successUrl = $this->getUrl('/user/' . $this->identity->id . '/friends/requests');
        $this->_ajaxOnSuccessUrl = $this->getUrl($path);
        $this->_ajaxCancelType = 'none';
        
        // Add page navigation to the footer if more than a page
        if ($pages->count() > 1) {
            $form['requests']['#footer'] = $this->PageNavRemote(
                'plugg-friends-requests-sent',
                $pages,
                $page->getPageNumber(),
                $this->getUrl($path, array('sort' => $sort, 'order' => $order))
            );
        }
        
        return $form;
    }
    
    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (empty($form->values['requests'])) return true;
        
        $model = $this->getPluginModel();
        $friend_requests = $model->Request->criteria()
            ->status_is(Plugg_Friends_Plugin::REQUEST_STATUS_PENDING)
            ->userId_is($this->identity->id)
            ->id_in($form->values['requests'])
            ->fetch();

        foreach ($friend_requests as $friend_request) {
            $friend_request->markRemoved();
        }

        return $model->commit();
    }
}