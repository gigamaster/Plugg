<?php
class Plugg_Form_Controller_Admin_Index extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $path = '/content/form';
        $this->_successUrl = $this->getUrl($path);
        
        $settings = array(
            'forms' => array(
                '#type' => 'tableselect',
                '#header' => array(),
                '#multiple' => true,
                '#js_select' => true,
                '#options' => array(),
            )
        );
        
        $sortable_headers = array(
            'title' => $this->_('Title'),
            'created' => $this->_('Date created'),
            'formentry_last' => $this->_('Last entry'),
            'formentry_count' => $this->_('Entries'),
        );
        $sort = $request->asStr('sort', 'formentry_last', array_keys($sortable_headers));
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
            $settings['forms']['#header'][$header_name] = $this->LinkToRemote(
                $header_label,
                'plugg-content',
                $this->getUrl($path, $url_params),
                array(),
                array(),
                $attr
            );
        }
        $settings['forms']['#header']['links'] = '';
        
        // Add rows (options)
        $pages = $this->getPluginModel()->Form->criteria()->paginate(20, $sort, $order);
        $page = $pages->getValidPage($request->asInt('p', 1));
        $forms = $page->getElements();
        foreach ($forms->with('LastFormentry', 'User') as $form) {
            $form_path = $path . '/' . $form->id;
            $links = array(
                $this->LinkToRemote($this->_('Details'), 'plugg-content', $this->getUrl($form_path)),
                $this->LinkToRemote($this->_('Edit'), 'plugg-content', $this->getUrl($form_path . '/edit')),
            );
            $title = h(mb_strimlength($form->title, 0, 100));
            $settings['forms']['#options'][$form->id] = array(
                'title' => !$form->hidden
                               ? $this->LinkTo($title, array('script' => 'main', 'base' => '/form', 'path' => $form->id), array('title' => $this->_('View this form')))
                               : $title,
                'created' => $this->DateTime($form->created),
                'formentry_last' => $form->LastFormentry
                                    ? sprintf(
                                        '%s<br /><small>%s</small>',
                                        $this->DateTime($form->LastFormentry->created),
                                        sprintf($this->_('(submitted by %s)'), $this->User_IdentityLink($form->LastFormentry->User)))
                                    : '',
                'formentry_count' => $form->formentry_count,
                'links' => implode(PHP_EOL, $links),
            );
            if ($form->hidden) $settings['forms']['#attributes'][$form->id]['@row']['class'] = 'shadow'; // @all for whole row
        }
        
        $this->_submitButtonLabel = $this->_('Delete');
        $this->_cancelUrl = null;
        
        $settings[$this->_submitButtonName]['hide'] = array(
            '#type' => 'submit',
            '#value' => $this->_('Hide'),
            '#submit' => array(array($this, 'hideForms')),
            '#weight' => -2,
        );
        $settings[$this->_submitButtonName]['unhide'] = array(
            '#type' => 'submit',
            '#value' => $this->_('Unide'),
            '#submit' => array(array(array($this, 'hideForms'), array(false))),
            '#weight' => -2,
        );
        $settings[$this->_submitButtonName]['empty'] = array(
            '#type' => 'submit',
            '#value' => $this->_('Empty'),
            '#submit' => array(array($this, 'emptyForms')),
            '#weight' => -1,
        );
        
        // Add page navigation to the footer if more than a page
        if ($pages->count() > 1) {
            $settings['forms']['#footer'] = $this->PageNavRemote(
                'plugg-content', $pages, $page->getPageNumber(), $this->getUrl($path, array('sort' => $sort, 'order' => $order))
            );
        }
        
        return $settings;
    }
    
    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (empty($form->values['forms'])) return true;
        
        $model = $this->getPluginModel();
        foreach ($model->Form->criteria()->id_in($form->values['forms'])->fetch() as $form) {
            $form->markRemoved();
        }
        
        return $model->commit();
    }
    
    public function hideForms(Plugg_Form_Form $form, $hide = true)
    {
        if (empty($form->values['forms'])) return true;
        
        $model = $this->getPluginModel();
        $forms = $model->Form->criteria()->hidden_is(!$hide)->id_in($form->values['forms'])->fetch();
        foreach ($forms as $form) {
            $form->hidden = $hide;
        }

        return  $model->commit();
    }
    
    public function emptyForms(Plugg_Form_Form $form)
    {
        if (empty($form->values['forms'])) return true;
        
        $model = $this->getPluginModel();

        // Delete all form data associated with the selected forms
        $criteria = $model->createCriteria('Formentry')->formId_in($form->values['forms']);
        if (false === $model->getGateway('Formentry')->deleteByCriteria($criteria)) {
            return false;
        }

        // Reset form statistics
        $forms = $model->Form->criteria()->id_in($form->values['forms'])->fetch();
        foreach ($forms as $form) {
            $form->set(array(
                'formentry_last' => 0,
                'formentry_count' => 0,
                'formentry_lasttime' => $form->created
            ));
        }

        return $model->commit();
    }
}