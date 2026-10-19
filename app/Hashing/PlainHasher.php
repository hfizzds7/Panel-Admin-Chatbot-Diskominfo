<?php

namespace App\Hashing;

use Illuminate\Contracts\Hashing\Hasher;

class PlainHasher implements Hasher
{
    public function make($value, array $options = [])
    {
        return (string) $value;
    }

    public function check($value, $hashedValue, array $options = [])
    {
        return (string) $value === (string) $hashedValue;
    }

    public function needsRehash($hashedValue, array $options = [])
    {
        return false;
    }

    public function info($hashedValue)
    {
        return [
            'algo' => 'plain',
            'algoName' => 'plain',
            'options' => [],
        ];
    }
}
