<?php

declare(strict_types=1);

namespace Example\Seller\Domain;

enum ProductType: string
{
    case DIGITAL_PRODUCTS = 'digital';
    case GAMBLING_SERVICES = 'gambling';
    case FINANCIAL_SERVICES = 'financial';
}