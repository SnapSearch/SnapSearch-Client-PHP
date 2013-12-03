Snapsearch Client PHP Generic
=============================

[![Build Status](https://travis-ci.org/Polycademy/SnapSearch-Client-PHP.png?branch=master)](https://travis-ci.org/Polycademy/SnapSearch-Client-PHP)

Snapsearch Client PHP Generic is PHP based framework agnostic HTTP client library for SnapSearch.


Installation
------------

Requires 5.3.3 or above and Curl.

Usage
-----

It is PSR-0 compatible.

Robots.json

Tests
----

Unit tests are written using Codeception. Codeception has already been bootstrapped (`codecept bootstrap`). To run tests use `codecept run` or `codecept run --debug` for debug messages. If you change the Codeception configuration files or add extra functions to the helpers make sure to run `codecept build` so that the settings take effect.

Tests won't run on a 5.3 PHP system.

Todo
----

Mocking Httpful: https://github.com/padraic/mockery/issues/33#issuecomment-28837090

Changing to JSON as an array: https://github.com/nategood/httpful/blob/master/examples/showclix.php

Investigate whether this library would be a better fit: https://github.com/rmccue/Requests