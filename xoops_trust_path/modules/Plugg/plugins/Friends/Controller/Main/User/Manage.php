<?php
class Plugg_Friends_Controller_Main_User_Manage extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $form = array(
            'friends' => array(
                '#type' => 'tableselect',
                '#header' => array('user' => '', 'username' => $this->_('User')),
                '#multiple' => true,
                '#js_select' => true,
                '#options' => array(),
            )
        );
        
        $sortable_headers = array(
            'created' => $this->_('Date added'),
        );
        $sort = $request->asStr('sort', 'created', array_keys($sortable_headers));
        $order = $request->asStr('order', 'DESC', array('ASC', 'DESC'));
        $path = '/user/' . $this->identity->id . '/friends/manage';
        
        // Add headers
        foreach ($sortable_headers as $header_name => $header_label) {
            $attr = array('title' => sprintf($this->_('Sort by %s'), $header_label));    
            if ($sort === $header_name) {
                $url_params = array('sort' => $header_name, 'order' => $order == 'ASC' ? 'DESC' : 'ASC');
                $attr['class'] = 'plugg-' . strtolower($order);
            } else {
                $url_params = array('sort' => $header_name, 'order' => 'ASC');
            }
            $form['friends']['#header'][$header_name] = $this->LinkToRemote(
                $header_label, 'plugg-content', $this->getUrl($path, $url_params), array(), array(), $attr
            );
        }
        $form['friends']['#header']['relationships'] = $this->_('Relationships');
        $form['friends']['#header']['links'] = '';
        
        // Add rows (options)
        $pages = $this->getPluginModel()->Friend->criteria()->userId_is($this->identity->id)
            ->paginate(20, $sort, $order);
        $friends = $pages->getValidPage($request->asInt('p', 1))->getElements();
        foreach ($friends->with('WithUser') as $friend) {
            $links = array(
                $this->LinkToRemote($this->_('Edit'), 'plugg-content', $this->getUrl($path . '/' . $friend->id . '/edit'))
            );
            $form['friends']['#options'][$friend->id] = array(
                'user' => $this->User_IdentityIcon($friend->WithUser),
                'username' => $this->User_IdentityLink($friend->WithUser),
                'created' => $this->DateTime($friend->created),
                'relationships' => h(implode($friend->getRelationships(), ', ')),
                'links' => implode(PHP_EOL, $links),
            );
        }
        
        $this->_submitButtonLabel = $this->_('Remove selected');
        $this->_cancelUrl = null;
        
        // Add page navigation to the footer if more than a page
        if ($pages->count() > 1) {
            $form['friends']['#footer'] = $this->PageNavRemote(
                'plugg-content', $pages, $page->getPageNumber(), $this->getUrl($path, array('sort' => $sort, 'order' => $order))
            );
        }
        
        return $form;
    }
    
    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (empty($form->values['friends'])) return true;
        
        $model = $this->getPluginModel();
        $friends = $model->Friend->criteria()->userId_is($this->identity->id)
            ->id_in($form->values['friends'])->fetch();
        foreach ($friends as $friend) {
            $friend->markRemoved();
        }
        
        return $model->commit();
    }
}