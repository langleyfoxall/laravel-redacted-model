<?php

namespace LangleyFoxall\RedactedModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RedactedModel extends Model
{

    /**
     * The fields that will be hidden or redacted
     * @var array $redacted
     */
    protected $redacted = [];

    /**
     * If redact is true a string will be returned instead of the value, otherwise it will be hidden
     * @var bool $redact
     */
    protected $redact = true;

    /**
     * The string that will be returned if redact is true
     * @var string
     */
    protected $redactedString = '[Hidden Data]';

    /**
     * Allow the user to override all protection
     * @var bool $protectionDisabled
     */
    private $protectionDisabled = false;

    /**
     * If the redacted value is null, should the key be hidden too.
     * @var bool $redactKeys
     */
    private $redactKeys = true;

    /**
     * Disabled protection for all fields
     */
    public function disableProtection()
    {
        $this->protectionDisabled = true;
    }

    /**
     * Enables protection for all fields
     */
    public function enableProtection()
    {
        $this->protectionDisabled = false;
    }

    /**
     * Allow protection to be enabled or disabled for certain keys
     * @param $key
     * @return bool
     */
    public function shouldRedactField($key)
    {
        return true;
    }

    /**
     * Determines if a field is one of the fields marked as protected redacted and the user
     * has specified it should be redacted
     * @param $key
     * @return bool
     */
    private function internalShouldRedactField($key)
    {
        return !$this->protectionDisabled && in_array($key, $this->redacted) && $this->shouldRedactField($key);
    }

    /**
     * Override of the laravel getAttribute
     * @param string $key
     * @return mixed|string|null
     */
    public function getAttribute($key)
    {
        return $this->internalShouldRedactField($key)
            ? $this->getDataForKey($key)
            : parent::getAttribute($key);
    }

    /**
     * Either return the default hidden value or use the user provided one
     * @param $key
     * @return string|null
     */
    private function getDataForKey($key)
    {
        $functionName = 'get'.Str::studly($key).'RedactedValue';
        return method_exists($this, $functionName)
            ? $this->{$functionName}($this->getOriginal($key))
            : $this->defaultRedactedValue($key, $this->getOriginal($key));
    }

    /**
     * By default return the redactedString or null, allows the user to override this
     * @param $key
     * @return string|null
     */
    public function defaultRedactedValue($key, $value)
    {
        return $this->redact ? $this->redactedString : null;
    }

    /**
     * Redact data if the model is access as an array
     * @return array|\Illuminate\Support\Collection
     */
    public function toArray()
    {
        $data = parent::toArray();
        $redactedData = [];

        foreach ($data as $key => $value) {
            if ($this->internalShouldRedactField($key)) {
                $value = $this->getAttribute($key);

                if ($this->redactKeys && is_null($value)) {
                    continue;
                }
            }

            $redactedData[$key] = $value;
        }

        return $redactedData;
    }

    /**
     * Set or append to the redacted fields
     * @param array|string $fields
     */
    public function setRedacted($fields)
    {
        if (is_array($fields)) {
            $this->redacted = $fields;
        } else {
            array_push($this->redacted, $fields);
        }

    }
}