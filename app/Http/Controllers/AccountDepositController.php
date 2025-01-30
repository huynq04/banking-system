<?php

namespace App\Http\Controllers;

use App\Dto\DepositDto;
use App\Exceptions\DepositAmountToLowException;
use App\Exceptions\InvalidAccountNumberException;
use App\Http\Requests\DepositRequest;
use App\Services\AccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountDepositController extends Controller
{
    private AccountService $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    /**
     * @throws InvalidAccountNumberException
     * @throws DepositAmountToLowException
     */
    public function store(DepositRequest $request): JsonResponse
    {
        $depositDto = new DepositDto();
        $depositDto->setAccountNumber($request->input('account_number'));
        $depositDto->setAmount($request->input('amount'));
        $depositDto->setDescription($request->input('description'));
        $this->accountService->deposit($depositDto);
        return $this->sendSuccess([], 'Deposit successful');
    }
}
