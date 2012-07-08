<?php
class Plugg_Aggregator_Controller_Admin_Feeds_Feed_Edit extends Plugg_Form_Controller
{   
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $this->_successUrl = $this->getUrl('/content/aggregator/feeds/' . $this->feed->id);
        $this->_submitButtonLabel = $this->_('Save');
        
        return $this->getPluginModel()->createForm($this->feed);
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $this->feed->title = $form->values['title'];
        $this->feed->description = $form->values['description'];
        $this->feed->site_url = $form->values['site_url'];
        $this->feed->feed_url = $form->values['feed_url'];
        $this->feed->language = $form->values['language'];
        $this->feed->favicon_url = $form->values['favicon_url'];
        $this->feed->favicon_hide = !empty($form->values['favicon_hide']);
        $this->feed->author_pref = $form->values['author_pref'];
        $this->feed->allow_image = !empty($form->values['allow_image']);
        $this->feed->allow_external_resources = !empty($form->values['allow_external_resources']);
        $this->feed->host = $form->values['host'];

        // Assign user if the value of owner is set
        if (isset($form->values['owner']) && strlen($form->values['owner'])) {
            $user = $this->getPlugin('User')->getIdentityFetcher()
                ->fetchUserIdentityByUsername($form->values['owner']);
            $this->feed->assignUser($user);
        } else {
            $this->feed->user_id = 0;
        }

        // Load feed info if feed and/or favicon URL is empty
        if (!$this->feed->feed_url || !$this->feed->favicon_url) {
            try {
                $this->getPlugin()->loadFeedInfo($this->feed);
            } catch (Plugg_Aggregator_Exception_InvalidSiteUrl $e) {
                $form->setError($this->_('Invalid site URL.'), 'site_url');

                return false;
            } catch (Plugg_Aggregator_Exception $e) {
                if (!$this->feed->feed_url) {
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
        }

        return $this->feed->commit();
    }
}