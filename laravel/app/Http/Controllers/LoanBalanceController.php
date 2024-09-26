<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateLoanBalanceScenario;
use Illuminate\Http\Request;

class LoanBalanceController extends Controller
{
    public static function calculateLoanBalance($loanValue, $interestRate, $months, $monthlyPayment, $currentMonth)
    {
        $compoundInterest = pow((1 + $interestRate / $months), $currentMonth);
        $loanBalance = $loanValue * $compoundInterest -
            $monthlyPayment * (($compoundInterest - 1) / ($interestRate / $months));
        return $loanBalance;
    }

    public function generateNormalLoanBalanceScenario($loanValue, $interestRateInDecimal, $term, $accountId, $monthsInYear = 12, $currency = 'AUD')
    {
        $monthlyPayment = PMT($interestRateInDecimal / $monthsInYear, $term * $monthsInYear, -$loanValue);

        $loanBalanceList = array_map(function ($data) use ($loanValue, $interestRateInDecimal, $monthsInYear, $monthlyPayment, $currency, $accountId) {
            static $index = -1;
            $index++;
            $deposit = 7785 * ($index - 1);

            return new GenerateLoanBalanceScenario([
                "month" => $index,
                "balance" => round(LoanBalanceController::calculateLoanBalance($loanValue, $interestRateInDecimal, $monthsInYear, $monthlyPayment, $index), 2),
                "deposit" => $deposit,
                "currency" => $currency,
                "loan_account_id" => $accountId,
                "scenario" => 'normal'
            ]);
        }, array_fill(0, $term * $monthsInYear, null));

        return $loanBalanceList;
    }
}
