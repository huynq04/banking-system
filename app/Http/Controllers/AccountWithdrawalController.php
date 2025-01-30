<?php

namespace App\Http\Controllers;

use App\Dto\WithdrawDto;
use App\Exceptions\ANotFoundException;
use App\Exceptions\WithdrawalAmountTooLowException;
use App\Http\Requests\WithdrawRequest;
use App\Services\AccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountWithdrawalController extends Controller
{
    private AccountService $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    /**
     * @throws ANotFoundException
     * @throws WithdrawalAmountTooLowException
     */
    public function store(WithdrawRequest $request): JsonResponse
    {
        $account = $this->accountService->getAccountByUserID($request->user()->id);
        $withdrawDto = new WithdrawDto();
        $withdrawDto->setAccountNumber($account->account_number);
        $withdrawDto->setAmount($request->input('amount'));
        $withdrawDto->setDescription($request->input('description'));
        $withdrawDto->setPin($request->input('pin'));
        $this->accountService->withdraw($withdrawDto);
        return $this->sendSuccess([], 'Withdrawal successful');
    }
}
