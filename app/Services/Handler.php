<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\BuyerDTO;
use App\Http\Controllers\Controller;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Handler extends Controller
{
    /* @var array */
    private $_buyer;

    public function run(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'age' => 'required|max:255|numeric',
            'gender' => 'required|max:20',
            'phone_number' => 'required|max:25',
            'email' => 'required|max:255|email:rfc,dns',
        ]);

        if ($validator->fails()) {
            return json_encode([
                'error' => $validator->errors()
            ]);
        }

        $this->_buyer = $validator->validated();

        $buyerDto = new BuyerDTO(
            $this->_buyer['first_name'],
            $this->_buyer['last_name'],
            (int)$this->_buyer['age'],
            $this->_buyer['gender'],
            (string)$this->_buyer['phone_number'],
            $this->_buyer['email'],
            (new DateTime())->setTimestamp(time())->format('d.m.Y'),
            (new DateTime())->getTimestamp()
        );

        return (new AmoCrmService())->execute($buyerDto);
    }
}
