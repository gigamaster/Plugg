<?php
class Plugg_Aggregator_Controller_Main_User_NewFeed extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        // Check if submitting for another user and have permission to do so
        if ($this->identity->id != $this->getUser()->id) {
            if (!$this->getUser()->hasPermission(array('aggregator feed add any', 'aggregator feed add any approved'))) return false;
        } else {
            if (!$this->getUser()->hasPermission(array('aggregator feed add own', 'aggregator feed add own approved'))) return false;
        }

        $this->_cancelUrl = $this->getUrl('/user/' . $this->identity->id . '/aggregator');

        $form = $this->getPluginModel()->createForm('Feed');
        unset($form['owner'], $form['title'], $form['description'], $form['language'], $form['favicon'], $form['options']['host']);
        if (!$this->getUser()->isSuperUser()) {
            if ($this->identity->id == $this->getUser()->id) {
                if (!$this->getUser()->hasPermission(array('aggregator feed allow own img', 'aggregator feed allow any img'))) {
                    unset($form['options']['allow_image']);
                }
                if (!$this->getUser()->hasPermission(array('aggregator feed allow own ex resources', 'aggregator feed allow any ex resources'))) {
                    unset($form['options']['allow_external_resources']);
                }
            } else {
                if (!$this->getUser()->hasPermission(array('aggregator feed allow any img'))) {
                    unset($form['options']['allow_image']);
                }
                if (!$this->getUser()->hasPermission(array('aggregator feed allow any ex resources'))) {
                    unset($form['options']['allow_external_resources']);
                }
            }
        }

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        // Create feed
        $feed = $this->getPluginModel()->create('Feed');
        $feed->markNew();
        
        foreach (array('site_url', 'feed_url') as $key) {
            if (!isset($form->settings[$key])) continue;
            
            $feed->$key = $form->values[$key];
        }
        
        foreach (array('author_pref') as $key) {
            if (!isset($form->settings['options'][$key])) continue;
            
            $feed->$key = $form->values[$key];
        }
        
        foreach (array('allow_image', 'allow_external_resources') as $key) {
            if (!isset($form->settings['options'][$key])) continue;
            
            $feed->$key = !empty($form->values[$key]);
        }

        // Load feed info
        try {
            $this->getPlugin()->loadFeedInfo($feed);
        } catch (Plugg_Aggregator_Exception_InvalidSiteUrl $e) {
            $form->setError($this->_('Invalid site URL.'), 'site_url');

            return false;
        } catch (Plugg_Aggregator_Exception $e) {
            if (!$feed->feed_url) {
                $form->setError(
                    $this->_('Failed fetching feed data. Make sure that the feed URL is dicoverable or manually enter the feed URL.'),
                    'feed_url'
                );
            } else {
                $form->setError(
                    $this->_('Failed fetching feed data from the supplied URL.'),
                    'feed_url'
                );
            }

            return false;
        }

        // Set feed status
        if ($this->getPlugin()->getConfig('feedsRequireApproval')) {
            $feed->status = Plugg_Aggregator_Plugin::FEED_STATUS_PENDING;
            if ($this->getUser()->hasPermission('aggregator feed add any approved')) {
                $feed->status = Plugg_Aggregator_Plugin::FEED_STATUS_APPROVED;
            } else {
                if ($this->identity->id == $this->getUser()->id &&
                    $this->getUser()->hasPermission('aggregator feed add own approved')
                ) {
                    $feed->status = Plugg_Aggregator_Plugin::FEED_STATUS_APPROVED;
                }
            }
        } else {
            $feed->status = Plugg_Aggregator_Plugin::FEED_STATUS_APPROVED;
            $success_msg = $this->_('Feed data submitted successfully.');
        }
        $feed->assignUser($this->identity);

        if (!$this->getPluginModel()->commit()) return false;

        if ($feed->isApproved()) {
            if ($this->identity->id != $this->getUser()->id &&
                $this->getPlugin()->getConfig('addededNotifyEmail', 'enable')
            ) {
                $feed->reload();
                $this->getPlugin()->sendFeedAddedEmail($feed);
            }
            $msg = $this->_('Feed data submitted successfully.');
        } else {
            $msg = $this->_('Feed data submitted successfully. The submitted feed will be listed on the feed list page once approved by the administrators.');
        }
        $response->setSuccess($msg);

        return true;
    }
}