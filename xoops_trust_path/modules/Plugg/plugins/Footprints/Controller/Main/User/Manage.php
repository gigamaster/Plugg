<?php
class Plugg_Footprints_Controller_Main_User_Manage extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $this->_submitable = $this->getUser()->hasPermission('footprints hide own');
        $this->_submitButtonLabel = $this->_('Hide selected');
        $this->_cancelUrl = null;
        
        $form = array(
            'footprints' => array(
                '#type' => 'tableselect',
                '#header' => array(),
                '#multiple' => true,
                '#js_select' => true,
                '#options' => array(),
                '#disabled' => !$this->_submitable,
            )
        );
        
        $sortable_headers = array(
            'created' => $this->_('Timestamp'),
        );
        $sort = $request->asStr('sort', 'created', array_keys($sortable_headers));
        $order = $request->asStr('order', 'DESC', array('ASC', 'DESC'));
        $path = '/user/' . $this->identity->id . '/footprints/manage';
        
        // Add headers
        foreach ($sortable_headers as $header_name => $header_label) {
            $attr = array('title' => sprintf($this->_('Sort by %s'), $header_label));    
            if ($sort === $header_name) {
                $url_params = array('sort' => $header_name, 'order' => $order == 'ASC' ? 'DESC' : 'ASC');
                $attr['class'] = 'plugg-' . strtolower($order);
            } else {
                $url_params = array('sort' => $header_name, 'order' => 'ASC');
            }
            $form['footprints']['#header'][$header_name] = $this->LinkToRemote(
                $header_label, 'plugg-content', $this->getUrl($path, $url_params), array(), array(), $attr
            );
        }
        $form['footprints']['#header']['summary'] = $this->_('Summary');
        
        // Add rows (options)
        $pages = $this->getPluginModel()->Footprint->criteria()->userId_is($this->identity->id)
            ->paginate(20, $sort, $order);
        $footprints = $pages->getValidPage($request->asInt('p', 1))->getElements();
        foreach ($footprints->with('TargetUser') as $footprint) {
            if ($footprint->hidden) {
                $form['footprints']['#attributes'][$footprint->id]['@row']['class'] = 'shadow'; // @all for whole row
            }
            $form['footprints']['#options'][$footprint->id] = array(
                'created' => $this->DateTime($footprint->created),
                'summary' => sprintf($this->_('Viewed the user profile page of %s.'), $this->User_IdentityLink($footprint->TargetUser))
            );
        }
        
        $form[$this->_submitButtonName]['unhide'] = array(
            '#type' => 'submit',
            '#value' => $this->_('Unhide selected'),
            '#submit' => array(array(array($this, 'submitForm'), array($request, false))),
            '#weight' => -1,
            '#disabled' => !$this->_submitable
        );
        
        // Add page navigation to the footer if more than a page
        if ($pages->count() > 1) {
            $form['footprints']['#footer'] = $this->PageNavRemote(
                'plugg-content', $pages, $page->getPageNumber(), $this->getUrl($path, array('sort' => $sort, 'order' => $order))
            );
        }
        
        return $form;
    }
    
    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, $hide = true)
    {
        if (empty($form->values['footprints'])) return true;
        
        $model = $this->getPluginModel();
        $footprints = $model->Footprint->criteria()->userId_is($this->identity->id)
            ->hidden_is(!$hide)->id_in($form->values['footprints'])->fetch();
        foreach ($footprints as $footprint) {
            $footprint->hidden = $hide;
        }
        
        return $model->commit();
    }
}