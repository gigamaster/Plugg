<?php
class Plugg_Form_Controller_Admin_Form_Entries extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $path = '/content/form/' . $this->form->id . '/entries';
        $this->_successUrl = $this->getUrl('/content/form/' . $this->form->id);
        
        $form = array(
            'formentries' => array(
                '#type' => 'tableselect',
                '#header' => array('user' => '', 'submitter' => $this->_('Submitter')),
                '#multiple' => true,
                '#js_select' => true,
                '#options' => array(),
            )
        );
        
        $sortable_headers = array(
            'created' => $this->_('Date created'),
            'ip' => $this->_('IP'),
        );
        $sort = $request->asStr('sort', 'created', array_keys($sortable_headers));
        $order = $request->asStr('order', 'DESC', array('ASC', 'DESC'));
        
        // Add headers
        foreach ($sortable_headers as $header_name => $header_label) {
            $attr = array('title' => sprintf($this->_('Sort by %s'), $header_label));    
            if ($sort === $header_name) {
                $url_params = array('sort' => $header_name, 'order' => $order == 'ASC' ? 'DESC' : 'ASC');
                $attr['class'] = 'plugg-' . strtolower($order);
            } else {
                $url_params = array('sort' => $header_name, 'order' => 'ASC');
            }
            $form['formentries']['#header'][$header_name] = $this->LinkToRemote(
                $header_label,
                'plugg-form-admin-form-entries',
                $this->getUrl($path, $url_params),
                array(),
                array(),
                $attr
            );
        }
        $form['formentries']['#header']['links'] = '';
        
        // Add rows (options)
        $pages = $this->form->paginateFormentries(20, $sort, $order);
        $formentries = $pages->getValidPage($request->asInt('p', 1))->getElements();
        foreach ($formentries->with('User') as $formentry) {
            $form_path = $path . '/' . $formentry->id;
            $links = array(
                $this->LinkToRemote($this->_('View'), 'plugg-content', $this->getUrl($form_path)),
            );
            $form['formentries']['#options'][$formentry->id] = array(
                'user' => $formentry->User ? $this->User_IdentityIcon($formentry->User) : '',
                'submitter' => $formentry->User ? $this->User_IdentityLink($formentry->User) : '',
                'created' => $this->DateTime($formentry->created),
                'ip' => h($formentry->ip),
                'links' => implode(PHP_EOL, $links),
            );
        }
        
        // Add page navigation to the footer if more than a page
        if ($pages->count() > 1) {
            $form['formentries']['#footer'] = $this->PageNavRemote(
                'plugg-form-admin-form-entries', $pages, $page->getPageNumber(), $this->getUrl($path, array('sort' => $sort, 'order' => $order))
            );
        }
        
        $this->_submitButtonLabel = $this->_('Delete');
        $this->_cancelUrl = null;
        $this->_ajaxCancelType = 'none';
        $this->_ajaxOnSuccessUrl = $this->getUrl($path);
        
        return $form;
    }
    
    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (empty($form->values['formentries'])) return true;
        
        $model = $this->getPluginModel();
        foreach ($model->Formentry->criteria()->id_in($form->values['formentries'])->fetch() as $formentry) {
            $formentry->markRemoved();
        }
        
        return $model->commit();
    }
}