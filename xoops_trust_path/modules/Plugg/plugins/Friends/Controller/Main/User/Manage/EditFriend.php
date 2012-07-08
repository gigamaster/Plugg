<?php
class Plugg_Friends_Controller_Main_User_Manage_EditFriend extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $this->_cancelUrl = $this->_successUrl = $this->getUrl('/user/' . $this->identity->id . '/friends/manage');

        $with_user = $this->getPlugin('User')->getIdentity($this->friend->with);
        $form['#header'][] = $this->_('Select personal relationships between you and your friend.');
        $form['friend'] = array(
            '#type' => 'item',
            '#title' => $this->_('Friend'),
            '#markup' => $this->User_IdentityThumbnail($with_user),
        );

        $xfn = $this->getPlugin()->getXFNMetaDataList();
        $empty_option = array('' => $this->_('No selection'));
        $relationships = $this->friend->getRelationships();

        $form['relationships'] = array(
            '#tree' => true,
            '#title' => $this->_('Relationships'),
            'friendship' => array(
                '#type' => 'radios',
                '#title' => $this->_('Friendship'),
                '#options' => array_merge(array_combine($xfn['Friendship'], $xfn['Friendship']), $empty_option),
                '#delimiter' => '&nbsp;',
                '#default_value' => ($v = array_intersect($relationships, $xfn['Friendship'])) ? $v : array(''),
            ),
            'physical' => array(
                '#type' => 'checkboxes',
                '#title' => $this->_('Physical'),
                '#options' => array_combine($xfn['Physical'], $xfn['Physical']),
                '#delimiter' => '&nbsp;',
                '#default_value' => $relationships,
            ),
            'professional' => array(
                '#type' => 'checkboxes',
                '#title' => $this->_('Professional'),
                '#options' => array_combine($xfn['Professional'], $xfn['Professional']),
                '#delimiter' => '&nbsp;',
                '#default_value' => $relationships,
            ),
            'geographical' => array(
                '#type' => 'radios',
                '#title' => $this->_('Geographical'),
                '#options' => array_merge(array_combine($xfn['Geographical'], $xfn['Geographical']), $empty_option),
                '#delimiter' => '&nbsp;',
                '#default_value' => ($v = array_intersect($relationships, $xfn['Geographical'])) ? $v : array(''),
            ),
            'family' => array(
                '#type' => 'radios',
                '#title' => $this->_('Family'),
                '#options' => array_merge(array_combine($xfn['Family'], $xfn['Family']), $empty_option),
                '#delimiter' => '&nbsp;',
                '#default_value' => ($v = array_intersect($relationships, $xfn['Family'])) ? $v : array(''),
            ),
            'romantic' => array(
                '#type' => 'checkboxes',
                '#title' => $this->_('Romantic'),
                '#options' => array_combine($xfn['Romantic'], $xfn['Romantic']),
                '#delimiter' => '&nbsp;',
                '#default_value' => $relationships,
            ),
        );

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if ($relationships = @$form->values['relationships']) {
            $rels = array();
            if ($friendship = @$relationships['friendship']) {
                $rels[] = $friendship;
            }
            if ($physical = @$relationships['physical']) {
                if (is_array($physical)) {
                    $rels = array_merge($rels, $physical);
                }
            }
            if ($professional = @$relationships['professional']) {
                if (is_array($professional)) {
                    $rels = array_merge($rels, $professional);
                }
            }
            if ($geographical = @$relationships['geographical']) {
                $rels[] = $geographical;
            }
            if ($family = @$relationships['family']) {
                $rels[] = $family;
            }
            if ($romantic = @$relationships['romantic']) {
                if (is_array($romantic)) {
                    $rels = array_merge($rels, $romantic);
                }
            }
            if ($identity = @$relationships['identity']) {
                if (is_array($identity)) {
                    $rels = array_merge($rels, $identity);
                }
            }
            if ($rel = trim(implode(' ', $rels))) {
                if ($this->friend->set('relationships', $rel)->commit()) {
                    return true;
                }
            }
        } else {
            $form->setError('relationships', $this->_('You must select at least one relationship.'));
        }

        return false;
    }
}