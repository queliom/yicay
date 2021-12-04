<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Repositories\TransferRepository;

use App\Exceptions\InsufficientFundsException;
use App\Exceptions\NotAConsumerException;
use App\Exceptions\NotAuthorizedServiceException;
use App\Exceptions\NotFoundPayeeException;
use App\Exceptions\NotFoundPayerException;
use App\Exceptions\PayerIsNotPayeeException;
use App\Exceptions\TimeBetweenTransactionNotToleratedException;

class TransferController extends Controller
{  
    private $repository;

    public function __construct(TransferRepository $repository)
    {   
        $this->repository = $repository;
    }

    public function create(Request $request)
    {   
        $rules = [
            'amount' => [
                'required', 
                'regex:/^\d*(\.\d{2})?$/', 
                'not_in:0'
            ],
            'payer' => [
                'required', 
                'uuid'
            ],
            'payee' => [
                'required', 
                'uuid'
            ]
        ];

        $fields = $request->only(['amount', 'payer', 'payee']);

        $validate = Validator::make($fields, $rules);

        if($validate->fails()) {
            return response()->json($validate->errors(), 400);
        }

        try {
            
            $result = $this->repository->processTransfer($fields);
            return response()->json($result, 200);

        } catch (NotFoundPayerException $e) {

            return response()->json([ 
                'errors' => ['payer' => $e->getMessage()] 
            ], 404);

        } catch (NotFoundPayeeException $e) {

            return response()->json([
                'errors' => ['payee' => $e->getMessage()] 
            ], 404);
            
        } catch (PayerIsNotPayeeException $e) {

            return response()->json([ 
                'errors' => ['transaction' => $e->getMessage()] 
            ], 409);

        } catch (NotAConsumerException $e) {

            return response()->json([ 
                'errors' => ['payer' => $e->getMessage()] 
            ], 401);
            
        } catch (TimeBetweenTransactionNotToleratedException $e) {

            return response()->json([ 
                'errors' => ['transaction' => $e->getMessage()] 
            ], 409);
            
        } catch (InsufficientFundsException $e) {

            return response()->json([ 
                'errors' => ['wallet' => $e->getMessage()] 
            ], 401);

        } catch (NotAuthorizedServiceException $e) {

            return response()->json([ 
                'errors' => ['transaction' => $e->getMessage()] 
            ], 401);

        }

    }
}
