<?php

namespace App\Services;

use App\Dtos\UserDto;
use App\Exceptions\AccountNumberExistsException;
use App\Exceptions\ANotFoundException;
use App\Interfaces\AccountServiceInterface;
use App\Models\Account;
use Illuminate\Database\Eloquent\Builder;

class AccountService implements AccountServiceInterface
{

    public function modelQuery(): Builder
    {
        return Account::query();
    }

    public function hasAccountNumber(UserDto $userDto): bool
    {
        return $this->modelQuery()->where('user_id', $userDto->getId())->exists();
    }

    /**
     * @throws AccountNumberExistsException
     */
    public function createAccountNumber(UserDto $userDto): Account
    {
        if ($this->hasAccountNumber($userDto)) {
            throw new AccountNumberExistsException();
        }
        /** @var Account */
        return $this->modelQuery()->create([
            'account_number' => substr($userDto->getPhoneNumber(), -10),
            'user_id' => $userDto->getId(),
        ]);
    }

    public function getAccountByAccountNumber(string $accountNumber): Account
    {
        // TODO: Implement getAccountByAccountNumber() method.
    }

    /**
     * @throws ANotFoundException
     */
    public function getAccountByUserID(int $userID): Account
    {
        $account = $this->modelQuery()->where('user_id', $userID)->first();
        if (!$account) {
            throw new ANotFoundException("Account number could not be found");
        }
        /** @var Account $account */
        return $account;
    }

    public function getAccount(int|string $accountNumberOrUserID): Account
    {
        // TODO: Implement getAccount() method.
    }
}
