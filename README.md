# Keypic
=======================

This library provide composer ready library to use [KeyPic][keypic_site] web service to spam detection, by using PSR-7 compliant requests.


## How to use
To use keypic web service with any request that implements PSR-7:

```php
   $keypicObj = new KeyPic\KeyPic($psrRequest);
   $keypicObj->setFormID('FORM_KEY_FROM_KEYPIC');
   $Token = $keypicObj->getToken('');

   $spam = $keypicObj->isSpam($Token, 'USER_EMAIL_VALUE', 'USER_NAME_VALUE');

  if($spam){
      echo "SPam detected";
  }
```

## How to Contribute

1. Fork this repository
2. Create a new branch for each feature or improvement
3. Send a pull request from each feature branch

It is very important to separate new features or improvements into separate feature branches,
and to send a pull request for each branch. This allows me to review and pull in new features
or improvements individually.

All pull requests must adhere to the [PSR-2 standard][psr2].

## System Requirements

* PHP 5.4.0+


## License

MIT Public License


[keypic_site]: http://keypic.com