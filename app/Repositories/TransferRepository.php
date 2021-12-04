<?php

namespace App\Repositories;

use App\Models\ConfirmationMessageQueue;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletPost;

use App\Handlers\TransactionHandler;
use App\Handlers\WalletHandler;

use App\Exceptions\InsufficientFundsException;
use App\Exceptions\NotAConsumerException;
use App\Exceptions\NotAuthorizedServiceException;
use App\Exceptions\NotFoundPayeeException;
use App\Exceptions\NotFoundPayerException;
use App\Exceptions\PayerIsNotPayeeException;
use App\Exceptions\TimeBetweenTransactionNotToleratedException;
use App\Services\AuthorizationService;

class TransferRepository
{   
    private $payload;

    public function processTransfer(array $payload) : array
    {
        $this->payload = $payload;

        $this->validate();

        $transactionId = Transaction::new($payload);

        Wallet::userDiscount($payload['payer'], $payload['amount']);

        WalletPost::debitEntry(
            Wallet::getIdByUser($payload['payer']),
            $transactionId,
            $payload['amount']
        );

        Wallet::userCredit($payload['payee'], $payload['amount']);

        WalletPost::creditEntry(
            Wallet::getIdByUser($payload['payee']),
            $transactionId,
            $payload['amount']
        );

        Transaction::setSituation($transactionId, 'success');

        ConfirmationMessageQueue::add('sms', 'This is a beautiful confirmation message.');

        return [ 'transaction_id' => $transactionId  ];

    }

    private function validate() {

        if(!User::exists($this->payload['payer'])) {
            throw new NotFoundPayerException("Not found");
        }

        if(!User::exists($this->payload['payee'])) {
            throw new NotFoundPayeeException("Not found");
        }
        
        if(!User::isConsumer($this->payload['payer'])) {
            throw new NotAConsumerException("Not a consumer");
        }

        if(!TransactionHandler::payerItsNotPayee($this->payload['payer'], $this->payload['payee'])) {
            throw new PayerIsNotPayeeException("Payer cannot pay for himself");
        }

        if(!TransactionHandler::timeBetweenEquivalentTransactionsIsTolerated($this->payload)) {
            throw new TimeBetweenTransactionNotToleratedException("Equivalent transaction in short time");
        }

        if(!WalletHandler::payerHasSufficientFunds($this->payload['payer'], $this->payload['amount'])) {
            throw new InsufficientFundsException("Insufficient funds");
        }

        if(!AuthorizationService::get()) {
            throw new NotAuthorizedServiceException("Not authorized");
        }

    }

}