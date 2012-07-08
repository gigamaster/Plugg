<?php
class Plugg_Aggregator_Controller_Main_User_Feed_Edit extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        if (!$this->getUser()->hasPermission('aggregator feed edit any')) {
            if (!$this->feed->isOwnedBy($this->getUser()
                || $this->getUser()->hasPermission('aggregator feed edit own'))
            ) {
                return false;
            }
        }

        $this->_cancelUrl = $this->_successUrl = $this->getUrl('/user/' . $this->identity->id . '/aggregator');

        $form = $this->getPluginModel()->createForm($this->feed);
        $user = $this->getUser();
        if (!$user->isSuperUser()) {
            unset($form['site_url'], $form['feed_url'], $form['favicon'], $form['language'], $form['owner']);
            if ($this->feed->isOwnedBy($this->getUser())) {
                if (!$this->getUser()->hasPermission(array('aggregator feed allow own img', 'aggregator feed allow any img'))) {
                    unset($form['options']['allow_image']);
                }
                if (!$this->getUser()->hasPermission(array('aggregator feed allow own ex resources', 'aggregator feed allow any ex resources'))) {
                    unset($form['options']['allow_external_resources']);
                }
                if (!$this->getUser()->hasPermission(array('aggregator feed edit own host', 'aggregator feed edit any host'))) {
                    unset($form['options']['host']);
                }
            } else {
                if (!$this->getUser()->hasPermission(array('aggregator feed allow any img'))) {
                    unset($form['options']['allow_image']);
                }
                if (!$this->getUser()->hasPermission(array('aggregator feed allow any ex resources'))) {
                    unset($form['options']['allow_external_resources']);
                }
                if (!$this->getUser()->hasPermission(array('aggregator feed edit any host'))) {
                    unset($form['options']['host']);
                }
            }
        } else {
            unset($form['site_url'], $form['feed_url']);
        }

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        foreach (array('title', 'description') as $key) {
            if (!isset($form->settings[$key])) continue;
            
            $this->feed->$key = $form->values[$key];
        }
        
        foreach (array('host', 'author_pref') as $key) {
            if (!isset($form->settings['options'][$key])) continue;
            
            $this->feed->$key = $form->values[$key];
        }
        
        foreach (array('allow_image', 'allow_external_resources') as $key) {
            if (!isset($form->settings['options'][$key])) continue;
            
            $this->feed->$key = !empty($form->values[$key]);
        }
        
        // Assign user if the value of owner is set
        if (isset($form->values['owner']) && strlen($form->values['owner'])) {
            $user = $this->getPlugin('User')->getIdentityFetcher()
                ->fetchUserIdentityByUsername($form->values['owner']);
            $this->feed->assignUser($user);
        } else {
            $this->feed->user_id = 0;
        }

        return $this->feed->commit();
    }
}