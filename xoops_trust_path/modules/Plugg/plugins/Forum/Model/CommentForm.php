<?php
class Plugg_Forum_Model_CommentForm extends Plugg_Forum_Model_Base_CommentForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        unset($settings['user_id'], $settings['Parent'], $settings['body']['#title']);
        
        $settings['_title']['#title'] = $this->_model->_('Add title');
        $settings['_title']['#collapsible'] = true;
        $settings['_title']['#collapsed'] = true;
        $settings['_title']['#weight'] = 6;
        $settings['_title']['title'] = $settings['title'];
        unset($settings['_title']['title']['#title'], $settings['title']);
        
        $settings['body']['#title'] = $this->_model->_('Message');
        $settings['body']['#type'] = 'filters_textarea';
        $settings['body']['#default_value'] = array(
            'text' => $entity->body,
            'filter_id' => $entity->body_filter_id,
        );
        
        $settings['attachments'] = array(
            '#weight' => 50,
            '#title' => $this->_model->_('Attachments'),
            '#type' => 'uploads_file',
            '#file_name_prefix' => 'forum_',
            '#collapsible' => true,
            '#collapsed' => true,
            '#current_files' => $entity->id ? $entity->getAttachmentFileIds() :null,
        );

        return $settings;
    }
}