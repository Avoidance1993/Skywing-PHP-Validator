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
    'username' => [
        'rules' => 'required|string|alphanum|max:15|min:5',
        'messages' => [
            'required' => 'Username is required.',
            'string'   => 'Username must be a string.',
            'alphanum' => 'Username can only contain letters and numbers.',
            'max'      => 'Username cannot be longer than 15 characters.',
            'min'      => 'Username must be at least 5 characters long.',
        ]
    ],
    'email' => [
        'rules' => 'required|string|email',
        'messages' => [
            'required' => 'Email is required.',
            'email'    => 'Email must be valid.',
        ]
    ],
    'password' => [
        'rules' => 'required|string|min:8|max:15|regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&\-_])/',
        'messages' => [
            'required' => 'Password is required.',
            'min'      => 'Password must be at least 8 characters.',
            'max'      => 'Password cannot exceed 15 characters.',
            'regex'    => 'Password must include lowercase, uppercase, number, and special character.',
        ]
    ],
    'confirm_password' => [
        'rules' => 'match:[password]',
        'messages' => [
            'match' => 'Passwords do not match.',
        ]
    ],
    'captcha_answer' => [
        'rules' => 'required|int',
        'messages' => [
            'required' => 'Captcha answer is required.',
            'int'      => 'Captcha answer must be a number.',
        ]
    ],
    'terms_conditions' => [
        'rules' => 'equals:1',
        'messages' => [
            'equals' => 'You must agree to the terms and conditions.',
        ]
    ]
];

// Instantiate the validator
$validator = new SkywingValidator($data, $schema);

// Use it
if ($validator->pass()) {
    echo "Validation passed! You can process the data.";
} else {
    print_r($validator->errors());
}



