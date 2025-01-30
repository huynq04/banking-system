<?php

namespace App\Services;

use App\Dto\AccountDto;
use App\Dto\TransactionDto;
use App\Enum\TransactionCategoryEnum;
use App\Interfaces\TransactionServiceInterface;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class TransactionService implements TransactionServiceInterface
{

    public function modelQuery(): Builder
    {
        return Transaction::query();
    }

    public function generateReference(): string
    {
        return Str::upper('TF' . '/' . Carbon::now()->getTimestampMs() . '/' . Str::random(4));
    }

    public function createTransaction(TransactionDto $transactionDto): Transaction
    {
        $data = [];
        if ($transactionDto->getCategory() == TransactionCategoryEnum::DEPOSIT->value) {
            $data = $transactionDto->forDepositToArray($transactionDto);
        }
        if ($transactionDto->getCategory() == TransactionCategoryEnum::WITHDRAWAL->value) {
            $data = $transactionDto->forWithdrawalToArray($transactionDto);
        }
        /** @var Transaction $transaction */
        $transaction = $this->modelQuery()->create($data);
        return $transaction;
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

    public function getTransactionByReference(string $reference): Transaction
    {
        // TODO: Implement getTransactionByReference() method.
    }

    public function getTransactionById(int $transactionID): Transaction
    {
        // TODO: Implement getTransactionById() method.
    }

    public function getTransactionsByAccountNumber(int $accountNumber, Builder $builder): Builder
    {
        // TODO: Implement getTransactionsByAccountNumber() method.
    }

    public function getTransactionsByUserId(int $userID, Builder $builder): Builder
    {
        return $builder->where('user_id', $userID);
    }

    public function downloadTransactionHistory(AccountDto $accountDto, Carbon $fromDate, Carbon $endDate): Collection
    {
        // TODO: Implement downloadTransactionHistory() method.
    }
}
