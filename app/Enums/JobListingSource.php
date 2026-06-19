<?php

namespace App\Enums;

enum JobListingSource: string
{
    case External = "external";
    case Module = "module";
    case Both = "both";
    //
}
