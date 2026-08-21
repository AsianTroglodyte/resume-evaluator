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
        $loopRan = "false";
        while (($row = fgetcsv($stream, null, ',', '"', '\\')) !== false) {
            $loopRan = "true";
            echo implode($row);
            if (count($row) !== 1) {
                throw ValidationException::withMessages([
                    'email_list' => 'There must be one column for all rows',
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
                    ['email_list' => "Rows {$currentRowNum} do not contain emails"]);
            }
            $rows[] = $row;
        }

        dump("loop ran: ", $loopRan);
        fclose($stream);

        $email_array = array_map(fn ($row) => $row[0], $rows);

        if (count($email_array) === 0) {
            if ($hasHeader) {
                throw ValidationException::withMessages(['email_list' => 'no emails given']);
            }
            if (!$hasHeader) {
                throw ValidationException::withMessages(['email_list' => 'No list content']);
            }
        }

        return $email_array;
    }
}


