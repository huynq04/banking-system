<?php

namespace App\Http\Controllers;

use App\Dtos\UserDto;
use App\Http\Requests\RegisterUserRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class AuthenticationController extends Controller
{

    public function __construct(public readonly UserService $userService)
    {
    }

    public function register(RegisterUserRequest $request): JsonResponse
    {
        $userDto = UserDto::fromAPiFormRequest($request);

        $user = $this->userService->createUser($userDto);

        return $this->sendSuccess(['user' => $user], "Registration Successful");
    }
}
