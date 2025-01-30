<?php

namespace App\Interfaces;

use App\Dto\TransferDto;
use App\Models\Transfer;
use Illuminate\Database\Eloquent\Builder;

interface TransferServiceInterface
{
    public function modelQuery(): Builder;

    public function createTransfer(TransferDto $transferDto): Transfer;

    public function generateReference(): string;
}
