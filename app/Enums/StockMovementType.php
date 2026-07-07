<?php

namespace App\Enums;

enum StockMovementType: string
{
    case OpeningBalance = 'opening_balance';
    case PurchaseReceipt = 'purchase_receipt';
    case PurchaseReturn = 'purchase_return';
    case ProductionIssue = 'production_issue';
    case ProductionOutput = 'production_output';
    case Sale = 'sale';
    case SaleReturn = 'sale_return';
    case TransferIn = 'transfer_in';
    case TransferOut = 'transfer_out';
    case Adjustment = 'adjustment';
}
