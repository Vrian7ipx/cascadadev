<?php

class ReportController extends \BaseController {

	public function report()
	{
		if (Input::all())
		{
			$groupBy = Input::get('group_by');
			$chartType = Input::get('chart_type');
			$startDate = Utils::toSqlDate(Input::get('start_date'), false);
			$endDate = Utils::toSqlDate(Input::get('end_date'), false);
		}
		else
		{
			$groupBy = 'MONTH';
			$chartType = 'Bar';
			$startDate = Utils::today(false)->modify('-3 month');
			$endDate = Utils::today(false);
		}

		$padding = $groupBy == 'DAYOFYEAR' ? 'day' : ($groupBy == 'WEEK' ? 'week' : 'month');		
		$endDate->modify('+1 '.$padding);
		$datasets = [];
		$labels = [];
		$maxTotals = 0;
		$width = 10;
		
		if (Auth::user()->account->isPro())
		{
			foreach ([ENTITY_INVOICE] as $entityType)
			{
				$records = DB::table($entityType.'s')
							->select(DB::raw('sum(amount) as total, '.$groupBy.'('.$entityType.'_date) as '.$groupBy))
							->where('account_id', '=', Auth::user()->account_id)
							->where($entityType.'s.deleted_at', '=', null)
							->where($entityType.'s.'.$entityType.'_date', '>=', $startDate->format('Y-m-d'))
							->where($entityType.'s.'.$entityType.'_date', '<=', $endDate->format('Y-m-d'))					
							->groupBy($groupBy);
							
				if ($entityType == ENTITY_INVOICE)
				{
					$records->where('is_quote', '=', false)
									->where('is_recurring', '=', false);
				}

				$totals = $records->lists('total');
				$dates = $records->lists($groupBy);		
				$data = array_combine($dates, $totals);
				
				$interval = new DateInterval('P1'.substr($groupBy, 0, 1));
				$period = new DatePeriod($startDate, $interval, $endDate);

				$totals = [];			

				foreach ($period as $d)
				{
					$dateFormat = $groupBy == 'DAYOFYEAR' ? 'z' : ($groupBy == 'WEEK' ? 'W' : 'n');				
					$date = $d->format($dateFormat);		
					$totals[] = isset($data[$date]) ? $data[$date] : 0;

					if ($entityType == ENTITY_INVOICE)  
					{
						$labelFormat = $groupBy == 'DAYOFYEAR' ? 'j' : ($groupBy == 'WEEK' ? 'W' : 'F');
						$label = $d->format($labelFormat);
						$labels[] = $label;
					}
				}

				$max = max($totals);

			}

			$width = (ceil( $maxTotals / 100 ) * 100) / 10;  
			$width = max($width, 10);
		}

		$dateTypes = [
			'DAYOFYEAR' => 'Daily',
			'WEEK' => 'Weekly',
			'MONTH' => 'Monthly'
		];

		$chartTypes = [
			'Bar' => 'Bar',
			'Line' => 'Line'
		];

		$params = [
			'labels' => $labels,
			'datasets' => $datasets,
			'scaleStepWidth' => $width,
			'dateTypes' => $dateTypes,
			'chartTypes' => $chartTypes,
			'chartType' => $chartType,
			'startDate' => $startDate->format(Session::get(SESSION_DATE_FORMAT)),
			'endDate' => $endDate->modify('-1'.$padding)->format(Session::get(SESSION_DATE_FORMAT)),
			'groupBy' => $groupBy,
			'feature' => ACCOUNT_CHART_BUILDER,
		];
		
		return View::make('reports.report_builder', $params);
	}
}