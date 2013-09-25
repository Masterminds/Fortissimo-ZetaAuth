#Fortissimo handlers for ZetaComponents Authentication

This provides Fortissimo commands to wrap the Authentication library
from ZetaComponents.

EXPERIMENTAL

What you get right now:

- session-based, htpasswd-backed authentication using ZetaComponents.

What we'd like to do in the future:

- Use the rest of the [Zeta Components Authentication library](https://github.com/zetacomponents/Authentication).

Pull requests welcome!

(We're also looking for an official Fortissimo-Sentry project that
incorporates [Catalyst Sentry](https://github.com/cartalyst/sentry).)


## Usage

Use composer, and add Masterminds/Fortissimo-ZetaAuth to your project.

```json
{
    "require": {
        "masterminds/fortissimo": "2.*",
        "masterminds/fortissimo-zetaauth": "dev-master",
    }
}
```

### Create an Htpasswd file

Use the Apache `htpasswd` program. It supports at least three hashing
algorithms: md5, crypt, and sha1. `-s` sets it to SHA1 (recommended).

```sh
$ htpasswd -s -c test.htpasswd myuser
```

That creates a file for you called `test.htpassword`

### Using the password inside of Fortissimo

Now you can use ZetaAuth to get password-based authentication in your
app:

```php
<?php
$registry->group("auth", "Validate user credentials or session.")
    ->does("\Fortissimo\ZetaAuth\HashSHA1", "ciphertext")
      ->using("cleartext")->from("post:password get:password")
    ->does('\Fortissimo\ZetaAuth\CheckHtpasswd', 'user')
      ->using("user", "")->from("post:user get:user")
      ->using("password", "")->from("cxt:ciphertext")
      ->using("htpasswd", "$basedir/test.htpasswd")
;

$registry->route("someRoute")->usesGroup("auth");

?>
```

To use hashes other than SHA1, you will need to write your own Hash
command for your desired algorithm. (Please fork this project and
contribute it back if it's generally useful!)



## TODO

This is a *very* basic implementation. The main check command should be
broken out to allow different session ahndler and to support a chain of
filters. While none of this is hard, all of it is outside of my current
needs.
