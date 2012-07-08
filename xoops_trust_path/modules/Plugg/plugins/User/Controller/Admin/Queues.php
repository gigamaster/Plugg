<?php
class Plugg_User_Controller_Admin_Queues extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $headers = array(
            'created' => $this->_('Created'),
            'type' => $this->_('Queue type'),
            'user' => $this->_('User/Email'),
            'links' => '',
        );
        
        $form = array(
            'queues' => array(
                '#type' => 'tableselect',
                '#header' => $headers,
                '#multiple' => true,
                '#js_select' => true,
                '#options' => array(),
            )
        );
        
        $sortable_headers = array('created', 'type');
        
        $sort = $request->asStr('sort', 'created', $sortable_headers);
        $order = $request->asStr('order', 'DESC', array('ASC', 'DESC'));
        $path = '/user/queues';
        
        // Add headers
        foreach ($sortable_headers as $header_name) {
            $header_label = $headers[$header_name];
            $attr = array('title' => sprintf($this->_('Sort by %s'), $header_label));    
            if ($sort === $header_name) {
                $url_params = array('sort' => $header_name, 'order' => $order == 'ASC' ? 'DESC' : 'ASC');
                $attr['class'] = 'plugg-' . strtolower($order);
            } else {
                $url_params = array('sort' => $header_name, 'order' => 'ASC');
            }
            $form['queues']['#header'][$header_name] = $this->LinkToRemote(
                $header_label, 'plugg-content', $this->getUrl($path, $url_params), array(), array(), $attr
            );
        }
        $form['queues']['#header']['links'] = '';
        
        // Add rows (options)
        $pages = $this->getPluginModel()->Queue->paginate(20, $sort, $order);
        $queues = $pages->getValidPage($request->asInt('p', 1))->getElements();
        foreach ($queues->with('User') as $queue) {
            $queue_path = $path . '/' . $queue->id;
            $links = array(
                $this->LinkTo($this->_('Process this queue'), $this->createUrl(array(
                    'script' => 'main',
                    'base' => '/user',
                    'path' => 'confirm/' . $queue->id,
                    'params' => array('key' => $queue->key, 'admin' => 1)
                ))),
                $this->LinkTo(
                    $this->_('Resend confirmation mail'),
                    $this->getUrl($queue_path . '/send', array('key' => $queue->key))
                ),
            );
            $form['queues']['#options'][$queue->id] = array(
                'created' => $this->DateTime($queue->created),
                'type' => $queue->getTypeStr(),
                'user' => $queue->identity_id && $queue->User ? $this->User_IdentityLink($queue->User) : h($queue->notify_email),
                'links' => implode(PHP_EOL, $links),
            );
        }
        
        $this->_submitButtonLabel = $this->_('Delete selected');
        $this->_cancelUrl = null;
        
        // Add page navigation to the footer if more than a page
        if ($pages->count() > 1) {
            $form['queues']['#footer'] = $this->PageNavRemote(
                'plugg-content', $pages, $page->getPageNumber(), $this->getUrl($path, array('sort' => $sort, 'order' => $order))
            );
        }
        
        return $form;
    }
    
    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (empty($form->values['queues'])) return true;
        
        $model = $this->getPluginModel();
        $queues = $model->Queue->criteria()->id_in($form->values['queues'])->fetch();
        foreach ($queues as $queue) {
            $queue->markRemoved();
        }
        
        return $model->commit();
    }
}