<?php

namespace Drupal\sc_sso_email_change_notifier\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements user update event api call.
 */
class UserUpdateEvent extends Event {

  /**
   * Event name.
   */
  const EVENT_NAME = 'sc_sso_email_change_notifier_update_user_event';

  /**
   * User data from form.
   *
   * @var \Drupal\Core\Form\FormStateInterface
   */
  protected FormStateInterface $userData;

  /**
   * @var $form
   */
  protected $form;

  /**
   * Class constructor.
   */
  public function __construct(FormStateInterface $userData, $form) {
    $this->userData = $userData;
    $this->form = $form;
  }

  /**
   * Gets user data from user form.
   *
   * @return \Drupal\Core\Form\FormStateInterface
   *   Users data from form.
   */
  public function getUserData() {
    return $this->userData;
  }

  public function getForm() {
    return $this->form;
  }

}
