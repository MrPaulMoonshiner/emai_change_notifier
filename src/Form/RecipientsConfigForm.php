<?php

namespace Drupal\sc_sso_email_change_notifier\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements form for settings up list of recipients.
 */
class RecipientsConfigForm extends ConfigFormBase {

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
    return 'recipients_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['#prefix'] = '<div id="recipient-settings-form-wrapper" >';
    $form['#suffix'] = '</div>';

    $form['add_new_recipient'] = [
      '#type' => 'button',
      '#value' => $this->t('Add new recipient'),
      '#name' => 'action-new-recipient',
      '#ajax' => [
        'callback' => '::addNewRecipient',
        'wrapper' => 'recipient-settings-form-wrapper',
      ],
    ];

    $form['recipients_list'] = [
      '#type' => 'container',
      '#title' => $this->t('Email'),
      '#tree' => TRUE,
    ];

    $user_input = $form_state->getUserInput();
    $default_values = isset($user_input['recipients_list'])
      ? $user_input['recipients_list']
      : $this->configFactory()->get(self::CONFIG_NAME)->get('sc_sso_list_of_recipients');

    $default_values = array_filter($default_values, 'is_string');
    // Adds on element for creating one blank line.
    $default_values[] = '';
    $form['recipients_list'] += $this->buildRecipientsList($default_values);

    return $form;
  }

  /**
   * Ajax callback.
   */
  public function addNewRecipient(array $form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * Provide builder for list of recipients.
   *
   * @param array $default_values
   *   Default configuration.
   *
   * @return array
   *   Part of form with new elements.
   */
  public function buildRecipientsList(array $default_values): array {
    $from = [];
    foreach ($default_values as $key => $email) {
      $form[$key] = [
        '#type' => 'email',
        '#title' => $this->t('Email #@key', ['@key' => $key + 1]),
        '#default_value' => $email,
        '#name' => 'recipients_list[' . $key . ']',
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $value = array_filter($form_state->getValue('recipients_list'));
    $value = array_unique($value);
    $value = array_values($value);
    $this->configFactory()
      ->getEditable(self::CONFIG_NAME)
      ->set('sc_sso_list_of_recipients', $value)
      ->save();
  }

}
