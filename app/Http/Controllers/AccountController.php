<?php

namespace App\Http\Controllers;

use App\Dtos\UserDto;
use App\Exceptions\AccountNumberExistsException;
use App\Services\AccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function __construct(private readonly AccountService $accountService)
    {
    }

    /**
     * @throws AccountNumberExistsException
     */
    public function store(Request $request): JsonResponse
    {
        $userDto = UserDto::fromModel($request->user());
        $account = $this->accountService->createAccountNumber($userDto);
        return $this->sendSuccess(['account' => $account], 'Account number generated successfully');
    }
}
