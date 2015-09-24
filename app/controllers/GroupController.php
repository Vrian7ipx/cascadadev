<?php

class GroupController extends \BaseController {

  public function getDatatable()
  {
    $query = DB::table('groups')
                ->where('groups.account_id', '=', Auth::user()->account_id)
                ->where('groups.deleted_at', '=', null)
                ->where('groups.public_id', '>=', 0)
                ->select('groups.public_id', 'groups.code', 'groups.name', 'groups.text');


    return Datatable::query($query)
      ->addColumn('code', function($model) { return link_to('groups/' . $model->public_id . '/edit', $model->code); })
      ->addColumn('name', function($model) { return nl2br(Str::limit($model->name, 100)); })
      ->addColumn('text', function($model) { return nl2br(Str::limit($model->text, 100)); })

      ->addColumn('dropdown', function($model) 
      { 
        return '<div class="btn-group tr-action" style="visibility:hidden;">
            <button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown">
              '.trans('texts.select').' <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" role="menu">
            <li><a href="' . URL::to('groups/'.$model->public_id) . '/edit">'.uctrans('texts.edit_group').'</a></li>                
            <li class="divider"></li>
            <li><a href="' . URL::to('groups/'.$model->public_id) . '/archive">'.uctrans('texts.archive_group').'</a></li>
          </ul>
        </div>';
      })       
      ->orderColumns(['code', 'name', 'text'])
      ->make();           
  }

  public function edit($publicId)
  {
    $data = [
      'showBreadcrumbs' => false,
      'group' => Group::scope($publicId)->firstOrFail(),
      'method' => 'PUT', 
      'url' => 'groups/' . $publicId, 
      'title' => trans('texts.edit_group')
    ];   
    return View::make('accounts.group', $data);   
  }

  public function create()
  {
    $data = [
      'showBreadcrumbs' => false,
      'group' => null,
      'method' => 'POST',
      'url' => 'groups', 
      'title' => trans('texts.create_group')
    ];
    return View::make('accounts.group', $data);       
  }

  public function store()
  {
    return $this->save();
  }

  public function update($publicId)
  {
    return $this->save($publicId);
  }  

  private function save($groupPublicId = false)
  {
    if ($groupPublicId)
    {
      $group = Group::scope($groupPublicId)->firstOrFail();
    }
    else
    {
      $group = Group::createNew();
    }

    $group->code = trim(Input::get('code'));

    $group->name = trim(Input::get('name'));

    $group->text = Input::get('text');

    $group->save();

    $message = $groupPublicId ? trans('texts.updated_group') : trans('texts.created_group');
    Session::flash('message', $message);

    return Redirect::to('company/groups');    
  }

  public function archive($publicId)
  {
    $group = Group::scope($publicId)->firstOrFail();
    $group->delete();

    Session::flash('message', trans('texts.archived_group'));
    return Redirect::to('company/groups');        
  }

}