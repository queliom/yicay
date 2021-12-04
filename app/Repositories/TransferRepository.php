<?php

namespace App\Repositories;

use App\Models\ConfirmationMessageQueue;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletPost;

use App\Handlers\TransactionHandler;
use App\Handlers\WalletHandler;

use App\Services\AuthorizationService;

use App\Exceptions\InsufficientFundsException;
use App\Exceptions\NotAConsumerException;
use App\Exceptions\NotAuthorizedServiceException;
use App\Exceptions\NotFoundPayeeException;
use App\Exceptions\NotFoundPayerException;
use App\Exceptions\PayerIsNotPayeeException;
use App\Exceptions\TimeBetweenTransactionNotToleratedException;

class TransferRepository
{   
    private array $payload;
    
    public function processTransfer(array $payload) : array
    {   
        $this->payload = $payload;

        $this->validate();

        /**
         * Creates new transaction "in process"
         */
        $transactionId = Transaction::new($payload);

        /**
         * Applies discount to the payer's wallet
         */
        Wallet::userDiscount($payload['payer'], $payload['amount']);

        /**
         * Creates debit posting register for the payer
         */
        WalletPost::debitEntry(
            Wallet::getIdByUser($payload['payer']),
            $transactionId,
            $payload['amount']
        );

        /**
         * Applies credit to the payee's wallet
         */
        Wallet::userCredit($payload['payee'], $payload['amount']);
        
        /**
         * Creates credit posting register for the payee
         */
        WalletPost::creditEntry(
            Wallet::getIdByUser($payload['payee']),
            $transactionId,
            $payload['amount']
        );

        /**
         * Sets transaction status to "success"
         */
        Transaction::setSituation($transactionId, 'success');

        /**
         * Adds send confirmation message to the queue
         */
        ConfirmationMessageQueue::add('sms', 'This is a beautiful confirmation message.');

        return [
            'transaction_id' => $transactionId 
        ];

    }

    private function validate() : void
    {   
        /**
         * Checks if the paying user exists
         */
        if(!User::exists($this->payload['payer'])) {
            throw new NotFoundPayerException("Not found");
        }

        /**
         * Checks if the payee user exists
         */
        if(!User::exists($this->payload['payee'])) {
            throw new NotFoundPayeeException("Not found");
        }

        /**
         * Checks if the payer is a consumer
         */
        if(!User::isConsumer($this->payload['payer'])) {
            throw new NotAConsumerException("Not a consumer");
        }

        /**
         * Checks that the payer is not paying for itself
         */
        if(!TransactionHandler::payerItsNotPayee($this->payload['payer'], $this->payload['payee'])) {
            throw new PayerIsNotPayeeException("Payer cannot pay for himself");
        }

        /**
         * Checks that there is no equivalent transaction 
         * within the non-tolerated time
         */
        if(!TransactionHandler::timeBetweenEquivalentTransactionsIsTolerated($this->payload)) {
            throw new TimeBetweenTransactionNotToleratedException("Equivalent transaction in short time");
        }

        /**
         * Checks if the payer has sufficient balance 
         * for transaction
         */
        if(!WalletHandler::payerHasSufficientFunds($this->payload['payer'], $this->payload['amount'])) {
            throw new InsufficientFundsException("Insufficient funds");
        }

        /**
         * Checks authorization mock
         */
        if(!AuthorizationService::get()) {
            throw new NotAuthorizedServiceException("Not authorized");
        }
    }

}