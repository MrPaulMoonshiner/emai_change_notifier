<?php

namespace Drupal\sc_sso_email_change_notifier\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements form for settings up list of recipients.
 */
class RecipientsEmailForm extends ConfigFormBase {

  /**
   * Save the configuration name.
   */
  const CONFIG_NAME = 'sc_sso_email_change_notifier.settings';

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [self::CONFIG_NAME];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'recipients_email_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['recipients_email'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Text of email notification on user change email action'),
      '#default_value' => $this->configFactory()->get(self::CONFIG_NAME)->get('sc_sso_text_of_email'),
      '#rows' => 12,
      '#required' => TRUE,
    ];
    $form['r_e_description'] = [
      '#type' => 'markup',
      '#markup' => $this->t(
      'Please write most likely notification email text on user change email action. </br>
        You can use this tokens: <br/>
        #username - will be changed by username of user who change email. </br>
        #uid - will be changed by user id of user who change email. </br>
        #new_email - will be changed by new email address of user who change email. </br>
        #old_email - will be changed by old email address of user who change email. </br>
        #date - will be changed by date when user who change email did this action. </br>'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $value = $form_state->getValue('recipients_email');
    $this->configFactory()
      ->getEditable(self::CONFIG_NAME)
      ->set('sc_sso_text_of_email', $value)
      ->save();
  }

}
