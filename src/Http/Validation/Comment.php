<?php

namespace App\Http\Validation;

use Respect\Validation\Validator;

class Comment
{

    private $errors = [];

    /**
     * @param array $q
     * @return bool
     * @todo #36 use a basic validation lib
     */
    public function verify(array $q): bool
    {
        if (!isset($q['text'])) {
            $this->errors[] = 'text is missing';
        } else {
            if (strlen(rtrim($q['text'])) < 3) {
                $this->errors[] = 'text is too short (minimum length: 3)';
            }

            if (strlen($q['text']) > 65535) {
                $this->errors[] = 'text is too long (minimum length: 65535)';
            }
        }

        if (! key_exists('parent', $q)) {
            $this->errors[] = 'parent is missing';
        } elseif(!is_null($q['parent']) && !is_int($q['parent'])) {
            $this->errors[] = 'parent must be an integer or null';
        }


        foreach (["text", "author", "website", "email"] as $k) {
            if (!key_exists($k, $q)){
                $this->errors[] = sprintf('% is missing', $k);
            } elseif (!is_null($q[$k]) && !is_string($q[$k])) {
                $this->errors[] = sprintf('%s must be a string or null', $k);

            }
        }
        unset($k);

        if (strlen((isset($q['email']) ? $q['email'] : '')) > 254) {
            $this->errors[] = 'http://tools.ietf.org/html/rfc5321#section-4.5.3';
        }


        if (isset($q['website'])) {
            $v = new Validator();
            $v->addRule(Validator::url());

            if (strlen($q['website']) > 254) {
                $this->errors[] = 'website is too long (minimum length: 254)';
            }

            if (strpos($q['website'], 'https://') === false && strpos($q['website'], 'http://') === false){
                $this->errors[] = 'website is invalid';
            } else {
                if (!$v->validate($q['website'])){
                    $this->errors[] = 'website is invalid';
                }
            }
        }

        return $this->isPassed();
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function isPassed(): bool
    {
        return count($this->errors) === 0;
    }

    public function hasFailed(): bool
    {
        return count($this->errors) > 0;
    }

}