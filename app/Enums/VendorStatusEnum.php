<?php

namespace App\Enums;

enum VendorStatusEnum :string
{
    case Pending = 'Pending';
    case Approved = 'Approved';
    case Rejected = 'Rejected';
}
