<?php

namespace App\Interfaces;


use App\Dto\AccountDto;
use App\Dto\DepositDto;
use App\Dto\TransferDto;
use App\Dto\UserDto;
use App\Dto\TransactionDto;
use App\Dto\WithdrawDto;
use App\Models\Account;
use Illuminate\Database\Eloquent\Builder;

interface AccountServiceInterface
{
    public function modelQuery(): Builder;

    public function createAccountNumber(UserDto $userDto): Account;

    public function getAccountByAccountNumber(string $accountNumber): Account;

    public function getAccountByUserID(int $userID): Account;

    public function getAccount(int|string $accountNumberOrUserID): Account;

    public function deposit(DepositDto $depositDto): TransactionDto;

    public function withdraw(WithdrawDto $withdrawDto): TransactionDto;

    public function transfer(
        string    $senderAccountNumber,
        string    $receiverAccountNumber,
        string    $senderAccountPin,
        int|float $amount,
        string    $description = null
    ): TransferDto;


    public function canWithdraw(AccountDto $accountDto, WithdrawDto $withdrawDto): bool;

    public function accountExist(Builder $accountQuery): void;
}
