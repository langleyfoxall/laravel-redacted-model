# Laravel Redacted Model
[![Packagist](https://img.shields.io/packagist/dt/langleyfoxall/laravel-redacted-model.svg)](https://packagist.org/packages/langleyfoxall/laravel-redacted-model/stats)

Laravel Redacted Model makes it easier to hide or modify fields on a model based on given conditions in order to reduce data leakage in Laravel applications.

## Installation

Laravel Redacted Model can be installed using composer. Run the following command in your project.

```bash
composer require langleyfoxall/laravel-redacted-model
```

If you have never used the Composer dependency manager before, head to the [Composer website](https://getcomposer.org/) for more information on how to get started.

# Usage

To redact fields simply extend `RedactedModel` in your model and set the `redacted` variable to an array of the fields you want to protect. By default when accesed these fields will return `[Hidden Data]`.

```php
class SensitiveModel extends RedactedModel
{
	protected $redacted = ['name'];
}
```

### Conditionally redacting data

To conditionally redact fields override `shouldRedactField` on your model. The name of the field will be passed into this method. This will return true by default until you override it.

_Note: Only fields specified in `$redacted` will be redacted regardless of what's returned from this method._

```php
class SensitiveModel extends RedactedModel
{
	protected $redacted = ['name'];
	
	public function shouldRedactField($key)
	{
		return !\Auth::user()->canSeeSensitiveFields();
	}
}
``` 


### Changing the default redacted string

To change the message returned you can set the `redactedString` on your model. This will then be returned instead of `[Hidden Data]`.

```php
class SensitiveModel extends RedactedModel
{
	protected $redacted = ['name'];
	
	protected $redactedString = '[Top Secret]';
}
``` 

### Hiding fields instead of redacting them

If you want to completely omit the field instead of redacting it you can set the `redact` variable on your model to false.

_Note: If `redactKeys` is set to true, when the model is serialised the keys of redacted fields will also be omitted._

```php
class SensitiveModel extends RedactedModel
{
	protected $redacted = ['name'];
	
	protected $redact = false;
}
``` 

By default the array key of fields that return `null` and are in the redacted fields list will too be omitted in case the field name is Sensitive. To disable this set `$redactKeys` to false on your model.

```php
class SensitiveModel extends RedactedModel
{
	protected $redacted = ['name'];
	
	protected $redactKeys = false;
}
``` 

### Redacted value accessors

Accesors can be used to define the value of specific fields if they're redacted. Redacted value accessors are defined the same way as [Laravel Accessors](https://laravel.com/docs/5.7/eloquent-mutators#accessors-and-mutators) but ending in `RedactedValue` instead of `Accessor`. 

The original value is passed into the method, this allows you to abstract the value instead of omitting or redacting it.

For example if instead of returning the name from the model you want to only return the first and last letter:

```php
class SensitiveModel extends RedactedModel
{
	protected $redacted = ['name'];
	
	public function getNameRedactedValue($value)
	{
		return subStr($value, 0, 1).'***'.subStr($value, -1 ,1);
	}
	
}

...

$instanceOfRedactedModel->name // Returns K***y instead of Kathryn Janeway
``` 

### Overriding the default redacted value

By default redacted values will be returned as `[Hidden Value]` or `null` depending on the value of `$redacted`. You can bypass this by overriding `defaultRedactedValue` on the model.

This is useful if you want to derive the redacted value from the original value, as the field name and original value are passed into it. For example if you want to replace all characters with stars:

```php
class SensitiveModel extends RedactedModel
{
	protected $redacted = ['name'];
	
	public function defaultRedactedValue($key, $value)
	{
		return str_repeat("*", strlen($value)); 
	}
}

...

$instanceOfRedactedModel->name // Returns ********** instead of Section 31

``` 

### Enabling and disabling protection

If you want to temporarily disable field redaction or omission you can call `disableProtection()` on the model to disable protection and `enableProtection()` to re-enable it. This has to be used on a per-instance basis.

```php
class SensitiveModel extends RedactedModel
{
	protected $redacted = ['name'];
}

...

$instanceOfRedactedModel->name // Returns [Hidden Data]

$instanceOfRedactedModel->disableProtection();

$instanceOfRedactedModel->name // Returns Reginald Barclay
```
 
