<?php

class BranchController extends \BaseController {

  public function getDatatable()
  {
    $query = DB::table('branches')
                ->where('branches.account_id', '=', Auth::user()->account_id)
                ->where('branches.deleted_at', '=', null)
                ->where('branches.public_id', '>', 0)
                ->select('branches.public_id', 'branches.name', 'branches.address1', 'branches.address2');


    return Datatable::query($query)
      ->addColumn('name', function($model) { return link_to('branches/' . $model->public_id . '/edit', $model->name); })
      ->addColumn('address1', function($model) { return nl2br(Str::limit($model->address1, 100)); })
      ->addColumn('address2', function($model) { return nl2br(Str::limit($model->address2, 100)); })
      ->addColumn('dropdown', function($model) 
      { 
        return '<div class="btn-group tr-action" style="visibility:hidden;">
            <button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown">
              '.trans('texts.select').' <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" role="menu">
            <li><a href="' . URL::to('branches/'.$model->public_id) . '/edit">'.uctrans('texts.edit_branch').'</a></li>                
            <li class="divider"></li>
            <li><a href="' . URL::to('branches/'.$model->public_id) . '/archive">'.uctrans('texts.archive_branch').'</a></li>
          </ul>
        </div>';
      })       
      ->orderColumns(['name', 'address1'])
      ->make();           
  }

  public function edit($publicId)
  {
    $data = [
      'showBreadcrumbs' => false,
      'branch' => Branch::scope($publicId)->firstOrFail(),
      'method' => 'PUT', 
      'url' => 'branches/' . $publicId, 
      'title' => trans('texts.edit_branch')
    ];

    $data = array_merge($data, self::getViewModel());     
    return View::make('accounts.branch', $data);   
  }

  public function create()
  {
    $data = [
      'showBreadcrumbs' => false,
      'branch' => null,
      'method' => 'POST',
      'url' => 'branches', 
      'title' => trans('texts.create_branch')
    ];
    $data = array_merge($data, self::getViewModel()); 
    return View::make('accounts.branch', $data);       
  }

  private static function getViewModel()
  {
    return [   

      'countries' => Country::remember(DEFAULT_QUERY_CACHE)->orderBy('name')->get(),
      'industries' => Industry::remember(DEFAULT_QUERY_CACHE)->orderBy('name')->get(),        
      
    ];
  }

  public function store()
  {
    return $this->save();
  }

  public function update($publicId)
  {
    return $this->save($publicId);
  }  

  private function save($branchPublicId = false)
  {
    if ($branchPublicId)
    {
      $branch = Branch::scope($branchPublicId)->firstOrFail();
    }
    else
    {
      $branch = Branch::createNew();
    }

    $branch->name = trim(Input::get('name'));
    $branch->address1 = trim(Input::get('address1'));
    $branch->address2 = trim(Input::get('address2'));
    $branch->city = trim(Input::get('city'));
    $branch->state = trim(Input::get('state'));
    $branch->postal_code = trim(Input::get('postal_code'));
    $branch->country_id = Input::get('country_id') ? Input::get('country_id') : null;  
    $branch->industry_id = Input::get('industry_id') ? Input::get('industry_id') : null;

    $branch->number_autho = Input::get('number_autho');
    $branch->deadline = Input::get('deadline');      
    $branch->key_dosage = Input::get('key_dosage');

    $branch->activity_pri = Input::get('activity_pri');      
    $branch->activity_sec1 = Input::get('activity_sec1');
    $branch->law = Input::get('law');

    $branch->save();

    $message = $branchPublicId ? trans('texts.updated_branch') : trans('texts.created_branch');
    Session::flash('message', $message);

    return Redirect::to('company/branches');    
  }

  public function archive($publicId)
  {
    $branch = Branch::scope($publicId)->firstOrFail();
    $branch->delete();

    Session::flash('message', trans('texts.archived_branch'));
    return Redirect::to('company/branches');        
  }

}