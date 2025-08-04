# Skywing-PHP-Validator

I was tired searching for a decent validator for my own CMS project that performs and validates the usual things when provided data and a schema, so I've made my own. Feel free to use and change this however you wish.

---
# Features

Zero dependencies, one file only

Simple and intuitive schema syntax

Common validation rules supported: required, email, min, max, match, int, bool, alphanum, and more

Easy to extend with your own validation rules

---
## Usage example

```php
require_once 'SkywingValidator.php';

$data = [
    'username'         => 'darkside106',
    'email'            => 'whatever.email@outlook.com',
    'password'         => 'somesecurepassword123',
    'confirm_password' => 'somesecurepassword123@',
    'captcha_answer'   => '245',
    'terms_conditions' => '0'
];

$schema = [
    'username'         => 'required|str|alphanum|max:15|min:5',
    'email'            => 'required|string|email',
    'password'         => 'required|string|min:8|max:15|regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&\-_])/',
    'confirm_password' => 'match:[password]',
    'captcha_answer'   => 'required|int',
    'terms_conditions' => 'match:[0,1]'
];

// Instantiate validator with data and schema
$validator = new SkywingValidator($data, $schema);

if ($validator->pass())
    echo "Validation passed! You can process the data.";
else 
    print_r($validator->errors());



