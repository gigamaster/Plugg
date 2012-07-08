<?php
class Plugg_Aggregator_Controller_Admin_Feeds_Add extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $form = $this->getPluginModel()->createForm('Feed');
        unset($form['title'], $form['description'], $form['language'], $form['options']['host']);
        $this->_submitButtonLabel = $this->_('Add feed');

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $feed = $this->getPluginModel()->create('Feed');
        $feed->markNew();
        
        foreach (array('site_url', 'feed_url', 'favicon_url') as $key) {
            if (!isset($form->settings[$key])) continue;
            
            $feed->$key = $form->values[$key];
        }
        
        foreach (array('author_pref') as $key) {
            if (!isset($form->settings['options'][$key])) continue;
            
            $feed->$key = $form->values[$key];
        }
        
        foreach (array('allow_image', 'allow_external_resources', 'favicon_hide') as $key) {
            if (!isset($form->settings['options'][$key])) continue;
            
            $feed->$key = !empty($form->values[$key]);
        }

        // Assign user if the value of owner is set
        if (isset($form->values['owner']) && strlen($form->values['owner'])) {
            $user = $this->getPlugin('User')->getIdentityFetcher()
                ->fetchUserIdentityByUsername($form->values['owner']);
            if (!$user->isAnonymous()) {
                $feed->assignUser($user);
            }
        }

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

        $feed->setApproved();

        if (!$feed->commit()) return false;

        // Send notification mail to the feed owner
        if (($feed_owner = $feed->user_id)
            && $this->getUser()->id != $feed_owner
        ) {
            $feed->reload();
            $this->getPlugin()->sendFeedAddedEmail($feed);
        }
        
        $this->getPlugin()->loadFeedItems($feed);

        $this->_successUrl = $this->getUrl('/content/aggregator/feeds/' . $feed->id);

        return true;
    }
}