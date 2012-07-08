<?php
class Plugg_User_Controller_Admin_Auths_Autologins extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $form = array(
            'autologins' => array(
                '#type' => 'tableselect',
                '#header' => array('user' => '', 'username' => $this->_('User')),
                '#multiple' => true,
                '#js_select' => true,
                '#options' => array(),
            )
        );
        
        $sortable_headers = array(
            'created' => $this->_('Created'),
            'updated' => $this->_('Updated'),
            'expires' => $this->_('Expires'),
        );
        $sort = $request->asStr('sort', 'expires', array_keys($sortable_headers));
        $order = $request->asStr('order', 'ASC', array('ASC', 'DESC'));
        $path = '/user/auths/autologins';
        
        // Add headers
        foreach ($sortable_headers as $header_name => $header_label) {
            $attr = array('title' => sprintf($this->_('Sort by %s'), $header_label));    
            if ($sort === $header_name) {
                $url_params = array('sort' => $header_name, 'order' => $order == 'ASC' ? 'DESC' : 'ASC');
                $attr['class'] = 'plugg-' . strtolower($order);
            } else {
                $url_params = array('sort' => $header_name, 'order' => 'ASC');
            }
            $form['autologins']['#header'][$header_name] = $this->LinkToRemote(
                $header_label, 'plugg-content', $this->getUrl($path, $url_params), array(), array(), $attr
            );
        }
        
        // Add rows (options)
        $pages = $this->getPluginModel()->Autologin->paginate(20, $sort, $order);
        $autologins = $pages->getValidPage($request->asInt('p', 1))->getElements();
        foreach ($autologins->with('User') as $autologin) {
            $form['autologins']['#options'][$autologin->id] = array(
                'user' => $this->User_IdentityIcon($autologin->User),
                'username' => $this->User_IdentityLink($autologin->User),
                'created' => $this->DateTime($autologin->created),
                'updated' => $autologin->updated ? $this->DateTime($autologin->updated) : '',
                'expires' => $this->DateTime($autologin->expires),
            );
        }
        
        $this->_submitButtonLabel = $this->_('Delete selected');
        $this->_cancelUrl = null;
        
        // Add page navigation to the footer if more than a page
        if ($pages->count() > 1) {
            $form['autologins']['#footer'] = $this->PageNavRemote(
                'plugg-content', $pages, $page->getPageNumber(), $this->getUrl($path, array('sort' => $sort, 'order' => $order))
            );
        }
        
        return $form;
    }
    
    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (empty($form->values['autologins'])) return true;
        
        $model = $this->getPluginModel();
        $autologins = $model->Autologin->criteria()->id_in($form->values['autologins'])->fetch();
        foreach ($autologins as $autologin) {
            $autologin->markRemoved();
        }
        
        return $model->commit();
    }
}