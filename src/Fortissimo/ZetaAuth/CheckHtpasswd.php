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

      foreach ($authentication->getStatus() as $status) {
        $this->context->log($status, "debug");
      }
      throw new \Fortissimo\InterruptException("Failed auth.");
    }

    return $user;
  }

}
