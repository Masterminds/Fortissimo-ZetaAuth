<?php

namespace \Fortissio\ZetaAuth;

/**
 * Password hasher for compatibility with htpasswd.
 *
 * Params:
 * - cleartext (string): The password as cleartext
 *
 * Returns:
 * - An SHA1-hashed base64 encoded hashcode.
 */
class HashSHA1 extends \Fortissimo\Command\Base {
  public function expects() {
    return $this
      ->description("Hash a string into a SHA1 and return the string version.")
      ->usesParam("cleartext", "The cleartext")
      ->andReturns("A string containing the hashcode.")
      ;
  }

  public function doCommand() {
    $cleartext = $this->param('cleartext', '');
    return base64_encode(sha1($cleartext, true));
  }

}

