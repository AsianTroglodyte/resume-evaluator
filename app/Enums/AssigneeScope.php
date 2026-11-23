<?php

namespace App\Enums;

enum AssigneeScope: string
{
    case Everyone = 'everyone';
    case Group = 'group';
    case Selected = 'selected';
}
