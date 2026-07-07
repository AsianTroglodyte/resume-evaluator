<?php

namespace App\Enums;

enum EvaluationStatus: string
{
    //
    case Pending = "pending";
    case Processing = "processing";
    case Completed = "completed";
    case Failed = "failed";
}
