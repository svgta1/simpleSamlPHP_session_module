# SimpleSAMLphp Session Source Module

This module has been created to verify if an user is authenticated by using his sessionId. It is usefull for a back office server who can't manage cookies. 

**Example** :

1. A webmail authenticate the users with SimpleSAMLphp.
2. The module add in attributes a token. This token is given to the IMAP server as the password.
3. The IMAP server access to the module to verify the authentication.



## Compatibility

The module has been tested with SimpleSAMLphp v1.19. It can't work with the v1.16 which don't allow to load a session with a sessionId.

The sessions handler must been set to memcache or redis or sql. Don't work with phpsession.



## Installation

You can download this module directely and install it in the module directory of SimpleSAMLphp in a new directory called **svgtasession**. 

Other solution with composer : 

```shell
composer require svgta/simplesamlphp-module-svgtasession
```

Check the config-template directory of the module and copy the config file in your config directory of SimpleSAMLphp.

Create in the <u>authorizedKeys.php</u> file a secure key. This will be necessary to the backend server to access to the module and avoid to been acceded by everyone. For exemple :

```shell
openssl rand -base64 24
```

Add the key in the array of the config file.  For example, you will have in the file <u>authorizedKeys.php</u> :

```php
$config = [
	'keys' => [
		'He3NcVl0cK0WZAO4SUgOC8UZPgvtuhQs',
	 ],
];
```



Finally, add the authoproc to the SP (in file **metada/saml20-sp-remote**)

```php
'authproc' => array(
	10 => array(
		'class' => 'core:PHP',
		'code' => '
			$attributes["token"] = [\SimpleSAML\Module\svgtasession\ses::setToken()];
		',
	),	
),
```

This will add the attribute **token** for the SP.

If you user SimpleSAMLphp on your SP application, you can generate the token by calling

```php
$token = \SimpleSAML\Module\svgtasession\ses::setToken();
```
after called the authentication. For example :
```php
$as = new \SimpleSAML\Auth\Simple('default-sp');
$as->requireAuth();
$token = \SimpleSAML\Module\svgtasession\ses::setToken();
```


## What is this token ?

The token is generated by using the sessionId and the authority used in the current session. It's a json encrypted with the key of the SimpleSAMLphp created for the main config file.

This token is uncrypted and controlled by the module when it's sended back by the backoffice server.



## URL to access 

The verifications must been done on the SimpleSAMLphp instance where the token has been generated.

To very if the session is avalable : 

```shell
https://yourSimpleSAMLphp_fqdn/authent/module.php/svgtasession/isAuth
```

To verify and receive the attributes 

```
https://yourSimpleSAMLphp_fqdn/authent/module.php/svgtasession/getData.php
```

They give back a json and a response status : 200 if the all is ok. 401 and 403 can been tested by the backoffice server two : 403 if the authentication key is not recognized, 401 if the data sent are not corresponding to a valid session. Other codes are error.

The json is like this : 

```json
{
	"success": true/false, -> if false status code other than 200
	"error": "the error" -> only if an error has been intercepted
	"data": attributes -> only if getData is used, given in form key=>value
}
```



## Access from backoffice

The backoffice server need to send the headers : 

```shell
'Accept: application/json'
'Content-Type: application/json'
'Authorization: Bearer key' -> The key must be one of these in the array of  **authorizedKeys.php** file.
```

------

**Troobleshooting on headers**

Your Nginx or Apache server may not send the Authorization header to simpleSAMLphp. In this case user the header X-Auth-Token

```shell
'Authorization: Bearer key' become 'X-Auth-Token: key'
```

------



The method must be **POST**.

The token must been sent in a json format :

```json
{"token": "the token given by \SimpleSAML\Module\svgtasession\ses::setToken()"}
```



Example with curl : 

```shell
curl -H 'Accept:application/json' \
	-H 'Authorization: Bearer He3NcVl0cK0WZAO4SUgOC8UZPgvtuhQs' \
	-H 'X-Auth-Token: He3NcVl0cK0WZAO4SUgOC8UZPgvtuhQs' \
	-d '{"token":"8b45ddabc9d0e2f4452371516829234a285fce7dde161e26173fbd53b5a7c8c9267d508ead8d3ac31280e585149b0fa8dbebe244a3f79d6cec1ae0ed11175424a01a9b97ef86ab81c5e85ca530d5217dad52267c99bb665b2da41cda3ccfee58784b0e86dab6b26a8ae25efbf166d52a90fcc46241d8aa8469c06ba42469ba01"}' \
	-H "Content-Type: application/json" \
	-X POST 'https://yourSimpleSAMLphp_fqdn/authent/module.php/svgtasession/getData.php'
```



