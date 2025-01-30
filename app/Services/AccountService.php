<?php

namespace App\Services;

use App\Dto\DepositDto;
use App\Dto\TransferDto;
use App\Dto\UserDto;
use App\Dto\AccountDto;
use App\Dto\TransactionDto;
use App\Dto\WithdrawDto;
use App\Events\TransactionEvent;
use App\Exceptions\AccountNumberExistsException;
use App\Exceptions\ANotFoundException;
use App\Exceptions\DepositAmountToLowException;
use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\InvalidAccountNumberException;
use App\Exceptions\InvalidPinException;
use App\Exceptions\WithdrawalAmountTooLowException;
use App\Interfaces\AccountServiceInterface;
use App\Models\Account;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;

class AccountService implements AccountServiceInterface
{

    private TransactionService $transactionService;
    private TransferService $transferService;
    private UserService $userService;

    public function __construct(TransactionService $transactionService, UserService $userService, TransferService $transferService)
    {
        $this->transactionService = $transactionService;
        $this->userService = $userService;
        $this->transferService = $transferService;
    }

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

    /**
     * @throws DepositAmountToLowException
     * @throws InvalidAccountNumberException
     */
    public function deposit(DepositDto $depositDto): TransactionDto
    {

        $minimum_deposit = 500;
        if ($depositDto->getAmount() < $minimum_deposit) {
            throw new DepositAmountToLowException($minimum_deposit);
        }
        try {
            DB::beginTransaction();
            $transactionDto = new TransactionDto();
            $accountQuery = $this->modelQuery()->where('account_number', $depositDto->getAccountNumber());
            $this->accountExist($accountQuery);
            /** @var Account $lockedAccount */
            $lockedAccount = $accountQuery->lockForUpdate()->first();
            $accountDto = AccountDto::fromModel($lockedAccount);
            $transactionDto = $transactionDto->forDeposit(
                $accountDto,
                $this->transactionService->generateReference(),
                $depositDto->getAmount(),
                $depositDto->getDescription(),
            );


            event(new TransactionEvent($transactionDto, $accountDto, $lockedAccount));
            DB::commit();
            return $transactionDto;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * @throws InvalidAccountNumberException
     */
    public function accountExist(Builder $accountQuery): void
    {
        if (!$accountQuery->exists()) {
            throw new InvalidAccountNumberException();
        }
    }

    /**
     * @throws WithdrawalAmountTooLowException
     * @throws \Exception
     */
    public function withdraw(WithdrawDto $withdrawDto): TransactionDto
    {
        $minimum_withdrawal = 500;
        if ($withdrawDto->getAmount() < $minimum_withdrawal) {
            throw new WithdrawalAmountTooLowException($minimum_withdrawal);
        }

        try {
            DB::beginTransaction();
            $accountQuery = $this->modelQuery()->where('account_number', $withdrawDto->getAccountNumber());
            $this->accountExist($accountQuery);
            /** @var Account $lockedAccount */
            $lockedAccount = $accountQuery->lockForUpdate()->first();
            $accountDto = AccountDto::fromModel($lockedAccount);

            if (!$this->userService->validatePin($accountDto->getUserId(), $withdrawDto->getPin())) {
                throw new InvalidPinException();
            }
            $this->canWithdraw($accountDto, $withdrawDto);
            $transactionDto = new TransactionDto();
            $transactionDto = $transactionDto->forWithdrawal(
                $accountDto,
                $this->transactionService->generateReference(),
                $withdrawDto
            );

            event(new TransactionEvent($transactionDto, $accountDto, $lockedAccount));
            DB::commit();
            return $transactionDto;

        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * @throws InsufficientBalanceException
     */
    public function canWithdraw(AccountDto $accountDto, WithdrawDto $withdrawDto): bool
    {
        // if the account is blocked
        // if the user has not exceeded their transaction limit for the day or month
        // if the amount to withdraw greater than the user balance
        if ($accountDto->getBalance() < $withdrawDto->getAmount()) {
            throw new InsufficientBalanceException();
        }
        // if the amount left after withdrawal is not more than the minimum account balance
        return true;
    }

    /**
     * @throws \Exception
     */
    public function transfer(
        string    $senderAccountNumber,
        string    $receiverAccountNumber,
        string    $senderAccountPin,
        int|float $amount,
        string    $description = null
    ): TransferDto
    {
        if ($senderAccountNumber == $receiverAccountNumber) {
            throw new  \Exception("Receiver and Sender Account number can not be the same");
        }
        try {
            DB::beginTransaction();
            $senderAccountQuery = $this->modelQuery()->where('account_number', $senderAccountNumber);
            $receiverAccountQuery = $this->modelQuery()->where('account_number', $receiverAccountNumber);

            $this->accountExist($senderAccountQuery);
            $this->accountExist($receiverAccountQuery);

            /** @var Account $lockedSenderAccount */
            $lockedSenderAccount = $senderAccountQuery->lockForUpdate()->first();
            /** @var Account $lockedReceiverAccount */
            $lockedReceiverAccount = $receiverAccountQuery->lockForUpdate()->first();
            $lockedSenderAccountDto = AccountDto::fromModel($lockedSenderAccount);
            $lockedReceiverAccountDto = AccountDto::fromModel($lockedReceiverAccount);

            if (!$this->userService->validatePin($lockedSenderAccountDto->getUserId(), $senderAccountPin)) {
                throw new InvalidPinException();
            }

            $transactionDto = new TransactionDto();
            $withdrawDto = new WithdrawDto();
            $depositDto = new DepositDto();
            $transferDto = new TransferDto();

            $withdrawDto->setAccountNumber($lockedSenderAccountDto->getAccountNumber());
            $withdrawDto->setAmount($amount);
            $withdrawDto->setDescription($description);
            $withdrawDto->setPin($senderAccountPin);

            $depositDto->setAccountNumber($lockedReceiverAccountDto->getAccountNumber());
            $depositDto->setAmount($amount);
            $depositDto->setDescription($description);

            $this->canWithdraw($lockedSenderAccountDto, $withdrawDto);

            $transactionWithdrawalDto = $transactionDto->forWithdrawal(
                $lockedSenderAccountDto,
                $this->transactionService->generateReference(),
                $withdrawDto
            );

            $transactionDepositDto = $transactionDto->forDeposit(
                $lockedReceiverAccountDto,
                $this->transactionService->generateReference(),
                $depositDto->getAmount(),
                $depositDto->getDescription(),
            );

            $transferDto->setReference($this->transferService->generateReference());

            $transferDto->setSenderId($lockedSenderAccountDto->getUserId());
            $transferDto->setSenderAccountId($lockedSenderAccountDto->getId());

            $transferDto->setRecipientAccountId($lockedReceiverAccountDto->getUserId());
            $transferDto->setRecipientId($lockedReceiverAccountDto->getId());

            $transferDto->setAmount($amount);
            $transferDto->setStatus('success');

            $transfer = $this->transferService->createTransfer($transferDto);

            $transactionWithdrawalDto->setTransferId($transfer->id);
            $transactionDepositDto->setTransferId($transfer->id);

            event(new TransactionEvent($transactionWithdrawalDto, $lockedSenderAccountDto, $lockedSenderAccount));
            event(new TransactionEvent($transactionDepositDto, $lockedReceiverAccountDto, $lockedReceiverAccount));
            DB::commit();
            return $transferDto;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }
}
