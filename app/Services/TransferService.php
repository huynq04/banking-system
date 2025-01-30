<?php

namespace App\Services;

use App\Dto\TransferDto;
use App\Interfaces\TransferServiceInterface;
use App\Models\Transfer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class TransferService implements TransferServiceInterface
{

    public function modelQuery(): Builder
    {
        return Transfer::query();
    }

    public function createTransfer(TransferDto $transferDto): Transfer
    {
        /** @var Transfer $transfer */
        $transfer = $this->modelQuery()->create([
            'sender_id' => $transferDto->getSenderId(),
            'recipient_id' => $transferDto->getRecipientId(),
            'sender_account_id' => $transferDto->getSenderAccountId(),
            'recipient_account_id' => $transferDto->getRecipientAccountId(),
            'reference' => $transferDto->getReference(),
            'status' => $transferDto->getStatus(),
            'amount' => $transferDto->getAmount(),
        ]);
        return $transfer;
    }

    public function generateReference(): string
    {
        return Str::upper('TRF' . '/' . Carbon::now()->getTimestampMs() . '/' . Str::random(4));
    }
}
