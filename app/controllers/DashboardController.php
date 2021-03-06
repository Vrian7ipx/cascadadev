<?php

class DashboardController extends \BaseController {

  public function index()
  {
    // total_income, billed_clients, invoice_sent and active_clients
    $select = DB::raw('COUNT(DISTINCT CASE WHEN invoices.id IS NOT NULL THEN clients.id ELSE null END) billed_clients,
                        SUM(CASE WHEN invoices.invoice_status_id >= '.INVOICE_STATUS_DRAFT.' THEN 1 ELSE 0 END) invoices_sent,
                        COUNT(DISTINCT clients.id) active_clients,
                        AVG(invoices.amount) as invoice_avg');
if (Utils::isAdmin()){
    $metrics = DB::table('accounts')
            ->select($select)
            ->leftJoin('clients', 'accounts.id', '=', 'clients.account_id')
            ->leftJoin('invoices', 'clients.id', '=', 'invoices.client_id')
            ->where('accounts.id', '=', Auth::user()->account_id)
            ->where('clients.deleted_at', '=', null)
            ->groupBy('accounts.id')
            ->first();
    
    $select = DB::raw('SUM(invoices.amount) value');

    $totalIncome = DB::table('accounts')
            ->select($select)
            ->leftJoin('clients', 'accounts.id', '=', 'clients.account_id')
            ->leftJoin('invoices', 'clients.id', '=', 'invoices.client_id')
            ->where('accounts.id', '=', Auth::user()->account_id)
            ->where('clients.deleted_at', '=', null)
            ->groupBy('accounts.id')
            ->first();

}
else{
    $metrics = DB::table('accounts')
            ->select($select)
            ->leftJoin('branches', 'accounts.id', '=', 'branches.account_id')
            ->leftJoin('clients', 'accounts.id', '=', 'clients.account_id')
            ->leftJoin('invoices', 'clients.id', '=', 'invoices.client_id')
            ->where('accounts.id', '=', Auth::user()->account_id)
            ->where('invoices.branch_id', '=', Auth::user()->branch_id)
            ->where('clients.deleted_at', '=', null)
            ->groupBy('branches.id')
            ->first();
    
    $select = DB::raw('SUM(invoices.amount) value');

    $totalIncome = DB::table('accounts')
            ->select($select)
            ->leftJoin('branches', 'accounts.id', '=', 'branches.account_id')
            ->leftJoin('clients', 'accounts.id', '=', 'clients.account_id')
            ->leftJoin('invoices', 'clients.id', '=', 'invoices.client_id')
            ->where('accounts.id', '=', Auth::user()->account_id)
            ->where('invoices.branch_id', '=', Auth::user()->branch_id)
            ->where('clients.deleted_at', '=', null)
            ->groupBy('branches.id')
            ->first();

}
           
            


    $activities = Activity::where('activities.account_id', '=', Auth::user()->account_id)
                ->where('activities.branch_id', '=', Auth::user()->branch_id)
                ->orderBy('created_at', 'desc')->take(6)->get();

    $pastDue = Invoice::scope()
                ->where('due_date', '<', date('Y-m-d'))
                ->where('balance', '>', 0)
                ->where('is_recurring', '=', false)
                ->where('is_quote', '=', false)
                ->orderBy('due_date', 'asc')->take(6)->get();

    $upcoming = Invoice::scope()
                  ->where('due_date', '>', date('Y-m-d'))
                  ->where('balance', '>', 0)
                  ->where('is_recurring', '=', false)
                  ->where('is_quote', '=', false)
                  ->orderBy('due_date', 'asc')->take(6)->get();

    $data = [
      'totalIncome' => Utils::formatMoney($totalIncome ? $totalIncome->value : 0, Session::get(SESSION_CURRENCY)),
      'billedClients' => $metrics ? $metrics->billed_clients : 0,
      'invoicesSent' => $metrics ? $metrics->invoices_sent : 0,
      'activeClients' => $metrics ? $metrics->active_clients : 0,
      'invoiceAvg' => Utils::formatMoney(($metrics ? $metrics->invoice_avg : 0), Session::get(SESSION_CURRENCY)),
      'activities' => $activities,
      'pastDue' => $pastDue,
      'upcoming' => $upcoming
    ];

    return View::make('dashboard', $data);
  }

}