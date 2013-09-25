<?php
namespace Fortissimo\ZetaAuth;

/**
 * ZetaCompoents authentication via Fortissimo.
 *
 * Presently, this supports htpasswd-based authentication with 
 * session-backed storage.
 *
 * Params:
 *
 * - user (string): username
 * - password (string): HASHED password. (See HashSHA1)
 * - htpasswd (string): Full path to the htpasswd file.
 * - routeFailuresTo (string): Where to send failed auth.
 *
 * Returns:
 * - The user object. This will be set even on failure so that you
 *   can use it for debugging/error messages.
 * - On failure, this will reroute to the route given in 
 * `routeFailuresTo` or to `@401` if no route is given.
 * - On failure this will set context value "${name}-error" to the library-generated 
 * error message.
 */
class CheckHtpasswd extends \Fortissimo\Command\Base {
  public function expects() {
    return $this
      ->description("Provide authentication services via ZetaComponents.")
      ->usesParam("user", "User name")
      ->usesParam("password", "Password")
      ->usesParam("htpasswd", "Full path to an htpasswd file.")
      ->usesParam("routeFailuresTo", "Name of route to which failures will be routed.")
      ->andReturns("The user name.")
      ;
  }

  public function doCommand() {

    $session = new \ezcAuthenticationSession();
    $session->start();

    $user = $this->param("user", $session->load());
    $pass = $this->param("password");
    $pwfile = $this->param("htpasswd");
    $routeTo = $this->param("routeFailuresTo", "@401");

    $credentials = new \ezcAuthenticationPasswordCredentials($user, $pass);
    $authentication = new \ezcAuthentication($credentials);
    $authentication->session = $session;
    $authentication->addFilter(new \ezcAuthenticationHtpasswdFilter($pwfile));

    if (!$authentication->run()) {
      $status = $authentication->getStatus();
      $err = $this->mostRidiculousErrorHandlingInTheUniverse($status);
      $this->context->log($err, 'error');

      $this->context->add($this->name . "-error", $err);
      $this->context->add($this->name, $user);

      //throw new \Fortissimo\InterruptException("Failed auth.");
      throw new  \Fortissimo\ForwardRequest($routeTo, $this->context, TRUE);
    }

    $this->context->log("Authenticated $user", "debug");
    return $user;
  }

  protected function mostRidiculousErrorHandlingInTheUniverse($status) {
    $errors =  array(
      'ezcAuthenticationHtpasswdFilter' => array(
          \ezcAuthenticationHtpasswdFilter::STATUS_USERNAME_INCORRECT => 'Incorrect username',
          \ezcAuthenticationHtpasswdFilter::STATUS_PASSWORD_INCORRECT => 'Incorrect password'
          ),
      'ezcAuthenticationSession' => array(
          \ezcAuthenticationSession::STATUS_EMPTY => '',
          \ezcAuthenticationSession::STATUS_EXPIRED => 'Session expired'
          )
        );

    $buffer = array();
    foreach ($status as $line) {
      list($k, $v) = each($line);
      $buffer[] = sprintf($errors[$k][$v]);
    }

    return implode("\n", $buffer);
  }

}
