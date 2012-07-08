<?php
class Plugg_Forum_Model_TopicForm extends Plugg_Forum_Model_Base_TopicForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);

        unset($settings['user_id']);

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
        
        $settings['options'] = array(
            '#title' => $this->_model->_('Options'),
            '#weight' => 70,
            '#collapsible' => true,
            '#collapsed' => true,
            'closed' => array(
                '#type' => 'checkbox',
                '#title' => $this->_model->_('Close this topic'),
                '#default_value' => $entity->closed == 1,
                '#description' => $this->_model->_('Check this option to mark the topic closed to disable any further comments on this topic.'),
            ),
            'sticky' => array(
                '#type' => 'checkbox',
                '#title' => $this->_model->_('Make this topic sticky'),
                '#default_value' => $entity->sticky == 1,
                '#description' => $this->_model->_('Check this option to have the topic "stick" to the top of the forum so that it will not fall down the list.'),
            ),
        );

        return $settings;
    }
}