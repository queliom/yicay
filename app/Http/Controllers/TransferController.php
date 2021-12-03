<?php

namespace App\Http\Controllers;

use App\Models\ConfirmationMessageQueue;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TransferController extends Controller
{   
    protected array $returnData;

    public function __construct()
    {
        $this->returnData = [
            'errors' => false
        ];    
    }

    public function create(Request $request)
    {   
        $rules = [
            'amount' => ['required', 'regex:/^\d*(\.\d{2})?$/', ''],
            'payer' => ['required', 'uuid'],
            'payee' => ['required', 'uuid']
        ];

        $validate = Validator::make($request->all(), $rules);

        if($validate->fails()) {
            $this->returnData['errors'] = $validate->errors();
            return response()->json($this->returnData['errors'], 400);
        }

        // Validar envolvidos

        $hasPayer = User::where('id', $request->input('payer'))->count();
        $hasPayee = User::where('id', $request->input('payee'))->count();
        
        if(!$hasPayer) {
            $this->returnData['errors'] = ['payer' => 'Not authorized'];
            return response()->json($this->returnData['errors'], 401);
        }

        if(!$hasPayee) {
            $this->returnData['errors'] = ['payee' => 'Not found'];
            return response()->json($this->returnData['errors'], 404);
        }

        // Validar tempo da ultima transação

        $runTime = DB::table('transactions')
            ->select(DB::raw('TIMESTAMPDIFF(SECOND, created_at, NOW()) AS seconds'))
            ->where('amount', $request->input('amount'))
            ->where('user_id_to', $request->input('payee'))
            ->where('user_id_from', $request->input('payer'))
            ->orderByDesc('created_at')
            ->first();

        if($runTime) {
            if($runTime->seconds <= Config::get('constants.transactions.last_tolerated_equivalence')) {
                $this->returnData['errors'] = ['payer' => 'Equivalent transaction in short time'];
                return response()->json($this->returnData['errors'], 401);
            }
        }

        // Validar se é consumidor

        $payerIsConsumer = User::where('id', $request->input('payer'))
            ->where('utype', 'consumer')
            ->count();

        if(!$payerIsConsumer) {
            $this->returnData['errors'] = ['payer' => 'Not a consumer'];
            return response()->json($this->returnData['errors'], 401);
        }

        // Criar transação

        $transactionId = Transaction::create([
            'id' => Str::uuid()->toString(),
            'user_id_to' => $request->input('payee'),
            'user_id_from' => $request->input('payer'),
            'amount' => $request->input('amount')
        ])->id;

        // Verificar saldo

        $payerBalance = Wallet::where('user_id', $request->input('payer'))
            ->where('balance', '>=', $request->input('amount'))
            ->get()
            ->count();

        if(!$payerBalance) {
            Transaction::where('id', $transactionId)
                ->update(['situation' => 'failed']);
                
            $this->returnData['errors'] = ['wallet' => 'Insufficient funds'];
            return response()->json($this->returnData['errors'], 401);
        }

        // Validar desconto no saldo

        $walletDebit = Wallet::where('user_id', $request->input('payer'))
            ->where('balance', '>=', $request->input('amount'))
            ->decrement('balance', $request->input('amount'));

        if(!$walletDebit) {
            Transaction::where('id', $transactionId)
                ->update(['situation' => 'failed']);
                
            $this->returnData['errors'] = ['wallet' => 'Transaction not completed'];
            return response()->json($this->returnData['errors'], 403);
        }

        $payerWalletId = Wallet::where('user_id', $request->input('payer'))
            ->get()
            ->first()
            ->id;

        WalletPost::create([
            'id' => Str::uuid()->toString(),
            'wallet_id' => $payerWalletId,
            'transaction_id' => $transactionId,
            'wallet_balance_before' => 0,
            'wallet_balance_after' => 0,
            'amount' => '-' . $request->input('amount')
        ]);

        // Validar saldo adicionado

        $payeeWalletId = Wallet::where('user_id', $request->input('payer'))
        ->get()
        ->first()
        ->id;

        Wallet::where('user_id', $request->input('payee'))
        ->increment('balance', $request->input('amount'));

        WalletPost::create([
            'id' => Str::uuid()->toString(),
            'wallet_id' => $payeeWalletId,
            'transaction_id' => $transactionId,
            'wallet_balance_before' => 0,
            'wallet_balance_after' => 0,
            'amount' => $request->input('amount')
        ]);

        // Validar sucesso na transação

        Transaction::where('id', $transactionId)
        ->update(['situation' => 'success']);

        // Enviar EMAIL

        ConfirmationMessageQueue::create([
            'id' => Str::uuid()->toString(),
            'mtype' => 'email',
            'body' => 'Pagamento realizado com sucesso!'
        ]);

        // Retornar ID da transação

        $this->returnData = [
            'transaction_id' => $transactionId
        ];

        return $this->returnData;

    }
}
