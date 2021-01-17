<?php

namespace App\Console\Commands;

use App\Http\Controllers\PaystackController;
use App\Models\Bill;
use App\Models\Loan;
use App\Models\Paystack;
use Illuminate\Console\Command;

class BillUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bill:users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bill users whose loans are due';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info($this->description);

        $todays_date = date('Y-m-d');

        $loans = Loan::where('status', 'active')->get();
        $paystack_controller = new PaystackController;

        foreach ($loans as $loan) {
            $loan_due_date = (date('Y-m-d', strtotime($loan->date_due)));

            if (strtotime($todays_date) >= strtotime($loan_due_date)) {
                
                $bill = $loan->bill;
                $paystack = Paystack::where('user_id', $loan->user->id)->first();

                if (!$paystack) {
                    
                    if ($bill) {
                        
                        $this->saveNewBillAmount($bill);
                    } else {
                        
                        $this->createNewBill($loan);
                    }
                    continue;
                }

                if (!$bill) {
                    
                    $charge_authorization = $paystack_controller->chargeAuthorization($paystack->authorization, $loan->amount, $loan->user->email);
                    
                    if ($charge_authorization === 'Charge attempted') {
                        
                        $this->saveNewDueDate($loan, $todays_date);
                        $this->endFailedBilling($bill);
                    } else {
                        
                        $this->createNewBill($loan);
                    }
                } else {
                    $charge_authorization = $paystack_controller->chargeAuthorization($paystack->authorization, $bill->amount, $loan->user->email);
                    
                    if ($charge_authorization === 'Charge attempted') {
                        
                        $this->saveNewDueDate($loan, $todays_date);
                        $this->endFailedBilling($bill);
                    } else {
                        
                        $this->saveNewBillAmount($bill);
                    }
                }
            }
        }
        $this->info('done');
    }

    private function saveNewDueDate($loan, $todays_date)
    {
        $time = strtotime('+30 day', strtotime($todays_date));
        $loan->date_due = date('Y-m-d', $time);
        $loan->save();

        return;
    }

    private function endFailedBilling($bill)
    {
        $bill->delete();

        return;
    }

    private function saveNewBillAmount($bill)
    {
        $bill->amount = ($bill->amount * 0.5 / 100) + $bill->amount;
        $bill->save();

        return;
    }

    private function createNewBill($loan)
    {
        Bill::create([
            'loan_id' => $loan->id,
            'amount' => ($loan->amount * 0.5 / 100) + $loan->amount
        ]);

        return;
    }
}
