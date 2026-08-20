<?php

use App\Support\ParseEmailList;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

uses(TestCase::class);

function emailStream(string $csv)
{
    $stream = fopen('php://memory', 'r+');
    fwrite($stream, $csv);
    rewind($stream);

    return $stream;
}

it('returns emails from a single column with an optional header', function () {
    $emails = (new ParseEmailList)(emailStream("email\none@southern.edu\ntwo@southern.edu\n"));

    expect($emails)->toBe(['one@southern.edu', 'two@southern.edu']);
});

it('keeps list with just emails', function () {
    $emails = (new ParseEmailList)(emailStream("one@southern.edu\ntwo@southern.edu"));

    expect($emails)->toBe(['one@southern.edu', 'two@southern.edu']);
});

it('rejects lists that are not a single column', function () {
    (new ParseEmailList)(emailStream("one@southern.edu,extra\n"));
})->throws(ValidationException::class);

it('rejects list with just email header', function () {
    (new ParseEmailList)(emailStream("email"));
})->throws(ValidationException::class);
