<?php

namespace Drupal\sc_sso_email_change_notifier\EventSubscriber;

use Drupal\Component\Datetime\Time;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Mail\MailManager;
use Drupal\sc_sso_email_change_notifier\Event\UserUpdateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Implements subscription on user update event.
 */
class UserUpdateSubscriber implements EventSubscriberInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * Using for gets current time.
   *
   * @var \Drupal\Component\Datetime\Time
   */
  protected Time $time;

  /**
   * Mail manager service.
   *
   * @var \Drupal\Core\Mail\MailManager
   */
  protected MailManager $mailManager;

  /**
   * Save the configuration name.
   */
  const CONFIG_NAME = 'sc_sso_email_change_notifier.settings';

  /**
   * UserRegistrationSubscriber class constructor.
   */
  public function __construct(ConfigFactoryInterface $configFactory, Time $time, MailManager $mail_manager) {
    $this->configFactory = $configFactory;
    $this->time = $time;
    $this->mailManager = $mail_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      UserUpdateEvent::EVENT_NAME => 'onEmailUpdate',
    ];
  }

  /**
   * Subscribe to the user logout event dispatched.
   *
   * @param \Drupal\sc_sso\Event\UserUpdateEvent $event
   *   Event object.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function onEmailUpdate(UserUpdateEvent $event) {
    $langcode = $event->getUserData()->get('user')->getPreferredLangcode();
    $module = 'sc_sso_email_change_notifier';
    $key = 'email-changing';
    $to = implode( ', ', $this->configFactory
      ->getEditable(self::CONFIG_NAME)
      ->get('sc_sso_list_of_recipients'));
    $params['message'] = $this->tokenReplace($event);
    $params['label'] = 'User change email address';
    $result = $this->mailManager->mail($module, $key, $to, $langcode, $params, NULL , TRUE);

    if ($result !== TRUE) {
      \Drupal::logger('sc_sso_email_change_notifier')->error("
      Error with sending email.
      Email notification about user changed email is failed.
      Test of email: {$this->tokenReplace($event)}");
    }
  }

  /**
   * Implements change token to value method for email message.
   *
   * @param \Drupal\sc_sso\Event\UserUpdateEvent $event
   *  event object.
   *
   * @return string
   *   Processed string message with changed tokens.
   */
  protected function tokenReplace($event) {
    $email = $this->configFactory
      ->getEditable(self::CONFIG_NAME)
      ->get('sc_sso_text_of_email');
    $form_state = $event->getUserData();
    $form = $event->getForm();
    $tokens = [
      '#username' => $form_state->get('user')->getAccountName(),
      '#uid' => $form_state->get('user')->id(),
      '#old_email' => $form["account"]["mail"]["#default_value"],
      '#new_email' => $form_state->getUserInput()['mail'],
      '#date' => date('Y-m-d H:i:s', $this->time->getCurrentTime()),
    ];
    foreach ($tokens as $token => $value) {
      $email = str_replace($token, $value, $email);
    }

    return $email;
  }

}
