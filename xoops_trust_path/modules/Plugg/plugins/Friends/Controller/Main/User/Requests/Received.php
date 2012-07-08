<?php
class Plugg_Friends_Controller_Main_User_Requests_Received extends Plugg_Form_Controller
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
            'created' => $this->_('Received on'),
        );
        $sort = $request->asStr('sort', 'created', array_keys($sortable_headers));
        $order = $request->asStr('order', 'ASC', array('ASC', 'DESC'));
        $path = '/user/' . $this->identity->id . '/friends/requests/received';
        
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
                $header_label, 'plugg-friends-requests-received', $this->getUrl($path, $url_params), array(), array(), $attr
            );
        }
        $form['requests']['#header']['message'] = $this->_('Message');
        
        // Add rows (options)
        $pages = $this->getPluginModel()->Request->criteria()->to_is($this->identity->id)
            ->status_is(Plugg_Friends_Plugin::REQUEST_STATUS_PENDING)->paginate(20, $sort, $order);
        $friend_requests = $pages->getValidPage($request->asInt('p', 1))->getElements();
        foreach ($friend_requests->with('User') as $friend_request) {
            $form['requests']['#options'][$friend_request->id] = array(
                'user' => $this->User_IdentityIcon($friend_request->User),
                'username' => $this->User_IdentityLink($friend_request->User),
                'created' => $this->DateTime($friend_request->created),
                'message' => h($friend_request->message),
            );
        }
        
        $form[$this->_submitButtonName]['accept'] = array(
            '#type' => 'submit',
            '#value' => $this->_('Accept selected'),
            '#submit' => array(array(array($this, 'acceptRequests'), array($request))),
            '#weight' => -1,
        );
        
        $this->_submitButtonLabel = $this->_('Reject selected');
        $this->_cancelUrl = null;
        $this->_successUrl = $this->getUrl('/user/' . $this->identity->id . '/friends/requests');
        $this->_ajaxOnSuccessUrl = $this->getUrl($path);
        $this->_ajaxCancelType = 'none';
        
        // Add page navigation to the footer if more than a page
        if ($pages->count() > 1) {
            $form['requests']['#footer'] = $this->PageNavRemote(
                'plugg-friends-requests-received',
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
            ->to_is($this->identity->id)
            ->id_in($form->values['requests'])
            ->fetch();

        if ($friend_requests->count() === 0) return true;

        foreach ($friend_requests as $friend_request) {
            $friend_request->setRejected();
        }

        if (!$model->commit()) return false;

        $this->DispatchEvent('FriendsRequestRejected', array($friend_requests));

        return true;
    }
    
    public function acceptRequests(Plugg_Form_Form $form, Sabai_Request $request)
    {
        if (empty($form->values['requests'])) return true;
        
        $model = $this->getPluginModel();
        $friend_requests = $model->Request->criteria()
            ->status_is(Plugg_Friends_Plugin::REQUEST_STATUS_PENDING)
            ->to_is($this->identity->id)
            ->id_in($form->values['requests'])
            ->fetch();

        if ($friend_requests->count() == 0) return true;

        foreach ($friend_requests as $friend_request) {
            $friend = $model->create('Friend');
            $friend->user_id = $friend_request->user_id;
            $friend->with = $friend_request->to;
            $friend->relationships = 'contact'; // Defaults to "contact" relationship
            $friend->markNew();

            $friend2 = $model->create('Friend');
            $friend2->user_id = $friend_request->to;
            $friend2->with = $friend_request->user_id;
            $friend2->relationships = 'contact'; // Defaults to "contact" relationship
            $friend2->markNew();

            $friend_request->markRemoved();
        }

        if (!$model->commit()) return false;

        $this->DispatchEvent('FriendsRequestAccepted', array($friend_requests));

        return true;
    }
}