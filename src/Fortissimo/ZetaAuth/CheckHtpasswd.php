<?php
namespace Fortissimo\ZetaAuth;

/**
 * ZetaCompoents authentication via Fortissimo.
 */
class CheckHtpasswd extends \Fortissimo\Command\Base {
  public function expects() {
    return $this
      ->description("Provide authentication services via ZetaComponents.")
      ->usesParam("user", "User name")
      ->usesParam("password", "Password")
      ->usesParam("htpasswd", "Full path to an htpasswd file.")
      ->andReturns("The user name.")
      ;
  }

  public function doCommand() {

    $session = new \ezcAuthenticationSession();
    $session->start();

    $user = $this->param("user", $session->load());
    $pass = $this->param("password");
    $pwfile = $this->param("htpasswd");

    $credentials = new \ezcAuthenticationPasswordCredentials($user, $pass);
    $authentication = new \ezcAuthentication($credentials);
    $authentication->session = $session;
    $authentication->addFilter( new \ezcAuthenticationHtpasswdFilter($pwfile));

    if (!$authentication->run()) {
      $status = $authentication->getStatus();
      $err = $this->mostRidiculousErrorHandlingInTheUniverse($status);
      $this->context->log($err, 'error');

      throw new \Fortissimo\InterruptException("Failed auth.");
    }

    return $user;
  }

  protected function mostRidiculousErrorHandlingInTheUniverse($status) {
    $errors =  array(
      'ezcAuthenticationHtpasswdFilter' => array(
          ezcAuthenticationHtpasswdFilter::STATUS_USERNAME_INCORRECT => 'Incorrect username',
          ezcAuthenticationHtpasswdFilter::STATUS_PASSWORD_INCORRECT => 'Incorrect password'
          ),
      'ezcAuthenticationSession' => array(
          ezcAuthenticationSession::STATUS_EMPTY => '',
          ezcAuthenticationSession::STATUS_EXPIRED => 'Session expired'
          )
        );

    $buffer = array();
    foreach ($status as $line) {
      list($k, $v) = each($line);
      $buffer[] = sprintf("Error: %s\n", $errors[$k][$v]);
    }

    return implode(' ', $buffer);
  }

}
