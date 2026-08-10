<?php

namespace App\Enums;

enum EvaluationStatus: string
{
    //
    case Processing = "processing";
    case Completed = "completed";
    case Failed = "failed";
}
