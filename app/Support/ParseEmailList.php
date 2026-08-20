<?php

namespace App\Support;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ParseEmailList
{
    /**
     * @param  resource  $stream
     * @return list<string>
     */
    public function __invoke($stream): array
    {
        $rows = [];

        $currentRowNum = 0;
        $hasHeader = False;
        while (($row = fgetcsv($stream, null, ',', '"', '\\')) !== false) {
            if (count($row) !== 1) {
                throw ValidationException::withMessages([
                    'emails_paste' => 'There must be one column for all rows',
                ]);
            }

            if ($row[0] === null) continue; // don't record empty rows

            $currentRowNum += 1;
            if ($currentRowNum === 1 && $row[0] === "email") {
                $hasHeader = True;
                continue;
            }

            if (!Validator::make(
                ['email' => $row[0]], 
                ['email' => 'email'])->passes()) {
                throw ValidationException::withMessages(
                    ['emails_paste' => 'One of the rows do not contain emails']);
            }
            $rows[] = $row;
        }

        if (count($rows) === 0) {
            if ($hasHeader) {
                throw ValidationException::withMessages(['emails_paste' => 'no emails given']);
            }
            if (!$hasHeader) {
                throw ValidationException::withMessages(['emails_paste' => 'No list content']);
            }
        }

        fclose($stream);
        $email_array = array_map(fn ($row) => $row[0], $rows);
        return $email_array;
    }
}


