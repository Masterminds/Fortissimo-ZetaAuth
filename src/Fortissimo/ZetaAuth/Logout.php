<?php
namespace Fortissimo\ZetaAuth;

/**
 * Log out of session-based auth.
 *
 * Generally, after logging out, you should route the user to a 
 * different page using a 3XX redirect.
 *
 * Params:
 *
 * Returns:
 *   - boolean true
 */
class Logout extends \Fortissimo\Command\Base {
  public function expects() {
    return $this
      ->description("Log out a user by destroying the session.")
      ->andReturns("Nothing.")
      ;
  }

  public function doCommand() {

    // Still need to start the session to load it.
    $session = new \ezcAuthenticationSession();
    $session->start();

    // This is just for logging.
    $user = $session->load();

    $session->destroy();

    $this->context->log("Logout $user", "info");

    return true;
  }
}
