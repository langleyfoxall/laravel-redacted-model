# Laravel Redacted Model

Laravel Redacted Model makes it easier to hide or modify fields on a modal based on given conditions.

## Installation

Laravel Redacted Model can be installed using composer. Run the following command in your project.

```bash
composer require langleyfoxall/laravel-redacted-modal
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

Redacted value accessors are defined the same way as [Laravel Accessors](https://laravel.com/docs/5.7/eloquent-mutators#accessors-and-mutators) but ending in `RedactedValue` instead of `Accessor`. 

The original value is passed into the method, this allows you to abstract the value instead of omitting or redacting it.

```php
class SensitiveModel extends RedactedModel
{
	protected $redacted = ['name'];
	
	public function getNameRedactedValue($value)
	{
		return subStr($value, 0).'***'.subStr($value, -1);
	}
}

...

$instanceOfRedactedModel->name // Returns K***y instead of Kathryn Janeway
``` 