<?php

namespace App\Services;

use App\Dto\TransactionDto;
use App\Dto\TransferDto;
use App\Enum\TransactionCategoryEnum;
use App\Models\Transaction;
use App\Models\Transfer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TransactionService
{

    public function modelQuery(): Builder
    {
        return Transaction::query();
    }
    public function generateReference(): string
    {
        return Str::upper('TF' . '/' . Carbon::now()->getTimestampMs() . '/' . Str::random(4));
    }

    public function createTransaction(TransactionDto $transactionDto): Builder|Model
    {
        $data = [];
        if ($transactionDto->getCategory() == TransactionCategoryEnum::DEPOSIT->value) {
            $data = $transactionDto->forDepositToArray($transactionDto);
        }

        if ($transactionDto->getCategory() == TransactionCategoryEnum::WITHDRAWAL->value) {
            $data = $transactionDto->forWithdrawalToArray($transactionDto);
        }

        return $this->modelQuery()->create($data);
    }
    public function updateTransactionBalance(string $reference, float|int $balance): void
    {
        $this->modelQuery()->where('reference', $reference)->update([
            'balance' => $balance,
            'confirmed' => true
        ]);
    }

    public function updateTransferID(string $reference, int $transferID): void
    {
        $this->modelQuery()->where('reference', $reference)->update([
            'transfer_id' => $transferID
        ]);
    }

}
