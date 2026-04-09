<?php

namespace App\Enums;

enum DrillWorkflowAction: string
{
    case SaveDraft = 'save_draft';
    case Submit = 'submit';
    case ReturnByBe = 'return_by_be';
    case Verify = 'verify';
    case ReturnByOim = 'return_by_oim';
    case Approve = 'approve';
    case Close = 'close';
}
