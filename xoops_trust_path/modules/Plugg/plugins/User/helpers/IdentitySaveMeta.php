<?php
class Plugg_Helper_User_IdentitySaveMeta extends Sabai_Application_Helper
{
    public function help(Sabai_Application $application, Sabai_User_AbstractIdentity $identity, $metaKey, $metaValue = null)
    {
        if (!$identity instanceof Plugg_User_Identity) {
            $identity = $application->User_Identity($identity, true);
            
            if ($identity->isAnonymous()) return false;
        }
        
        // Convert to array of meta data if not an array
        if (!is_array($metaKey)) $metaKey = array($metaKey => $metaValue);

        $model = $application->getPlugin('User')->getModel();
        // Update meta data already in the database 
        foreach ($model->Meta->criteria()->key_in(array_keys($metaKey))->fetchByUser($identity->id) as $meta) {
            if (!array_key_exists($meta->key, $metaKey)) continue;
            if (is_scalar($metaKey[$meta->key])) {
                $meta->value = $metaKey[$meta->key];
            } else {
                $meta->value = serialize($metaKey[$meta->key]);
                $meta->serialized = 1;
            }
            unset($metaKey[$meta->key]);
        }
        // Create meta data that are not yet in the database
        foreach ($metaKey as $meta_key => $meta_value) {
            $meta = $model->create('Meta');
            $meta->key = $meta_key;
            $meta->assignUser($identity);
            $meta->markNew();
            if (is_scalar($meta_value)) {
                $meta->value = $meta_value;
            } else {
                $meta->value = serialize($meta_value);
                $meta->serialized = 1;
            }
        }

        return $model->commit();
    }
}