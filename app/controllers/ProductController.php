<?php

use ninja\repositories\ProductRepository;

class ProductController extends \BaseController {

  protected $ProductRepo;

  public function __construct(ProductRepository $ProductRepo)
  {
    parent::__construct();

    $this->ProductRepo = $ProductRepo;
  }

    /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function index()
  {
    return View::make('list', array(
      'entityType'=>ENTITY_PRODUCT, 
      'title' => trans('texts.products'),
      'columns'=>Utils::trans(['checkbox', 'product_cod', 'notes', 'pack_types', 'ice', 'units', 'cc', 'dropdown'])
    ));   
  }
  public function getDatatable()
  {     
      $products = $this->ProductRepo->find(Input::get('sSearch'));

        return Datatable::query($products)
          ->addColumn('checkbox', function($model) { return '<input type="checkbox" name="ids[]" value="' . $model->public_id . '">'; })
          ->addColumn('product_key', function($model) { return link_to('products/' . $model->public_id, $model->product_key); })
          ->addColumn('notes', function($model) { return nl2br(Str::limit($model->notes, 50)); })
          ->addColumn('pack_types', function($model) { return link_to('products/' . $model->public_id, $model->pack_types); })
          ->addColumn('ice', function($model) { return link_to('products/' . $model->public_id, $model->ice ? 'Si' : 'No'); })
          ->addColumn('units', function($model) { return link_to('products/' . $model->public_id, $model->units); })
          ->addColumn('cc', function($model) { return link_to('products/' . $model->public_id, $model->cc); })
          ->addColumn('dropdown', function($model) 
          { 
            return '<div class="btn-group tr-action" style="visibility:hidden;">
                <button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown">
                  '.trans('texts.select').' <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" role="menu">
                <li><a href="' . URL::to('products/'.$model->public_id) . '/edit">'.uctrans('texts.edit_product').'</a></li>                
                <li class="divider"></li>
                <li><a href="' . URL::to('products/'.$model->public_id) . '/archive">'.uctrans('texts.archive_product').'</a></li>
              </ul>
            </div>';
          })        
          ->make();         
  }


  /**
   * Store a newly created resource in storage.
   *
   * @return Response
   */
  public function store()
  {
    return $this->save();
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return Response
   */
  public function show($publicId)
  {
    $product = Product::withTrashed()->scope($publicId)->with('prices')->firstOrFail();
    Utils::trackViewed($product->getDisplayName(), ENTITY_PRODUCT);
  



    $data = array(
      'showBreadcrumbs' => false,
      'product' => $product,
      'title' => trans('texts.view_product')
    );

    return View::make('products.show', $data);
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return Response
   */
  public function create()
  {
    $data = [
      'product' => null,
      'method' => 'POST',
      'url' => 'products', 
      'title' => trans('texts.new_product')
    ];

    $data = array_merge($data, self::getViewModel()); 
    return View::make('products.edit', $data);      
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return Response
   */
  public function edit($publicId)
  {
      $product = Product::scope($publicId)->with('prices')->firstOrFail();
      $data = [
        'product' => $product,
        'method' => 'PUT', 
        'url' => 'products/' . $publicId, 
        'title' => trans('texts.edit_product')
      ];
      $data = array_merge($data, self::getViewModel()); 
    // return Response::json($data);
      return View::make('products.edit', $data);   
  }


    private static function getViewModel()
  {
    return [    
      'price_types' => PriceType::remember(DEFAULT_QUERY_CACHE)->orderBy('name')->get(),
    ];
  }

   /**
   * Update the specified resource in storage.
   *
   * @param  int  $id
   * @return Response
   */
  public function update($publicId)
  {
    return $this->save($publicId);
  }  

  private function save($publicId = null)
  {
      if ($publicId)
      {
        $product = Product::scope($publicId)->firstOrFail();
      }
      else
      {
        $product = Product::createNew();
      }

      $product->product_key = trim(Input::get('product_key'));
      $product->notes = trim(Input::get('notes'));
      $product->pack_types = trim(Input::get('pack_types'));
      $product->ice = trim(Input::get('ice'));
      $product->units = trim(Input::get('units'));
      $product->cc = trim(Input::get('cc'));
      $product->save();

        $data = json_decode(Input::get('data'));
        $pricesIds = [];
        $id = 1;
        
        foreach ($data->prices as $price)
        {
          if (isset($price->public_id) && $price->public_id)
          {
            $record = Price::scope($price->public_id)->firstOrFail();
          }
          else
          {
            $record = Price::createNew();
          }
          $record->price_type_id = $id;

          $record->cost = trim($price->cost);

          $product->prices()->save($record);
          $priceIds[] = $record->public_id; 
          $id++;        
        }

        foreach ($product->prices as $price)
        {
          if (!in_array($price->public_id, $priceIds))
          { 
            $price->delete();
          }
        }
            
      if ($publicId) 
      {
        Session::flash('message', trans('texts.updated_product'));
      } 
      else 
      {
        // Activity::createProduct($product);
        Session::flash('message', trans('texts.created_product'));
      }

      return Redirect::to('products/' . $product->public_id);
  
  }

  public function archive($publicId)
  {
    $product = Product::scope($publicId)->firstOrFail();
    $product->delete();

    Session::flash('message', trans('texts.archived_product'));
    return Redirect::to('products');        
  }

  public function bulk()
  {
    $action = Input::get('action');
    $ids = Input::get('id') ? Input::get('id') : Input::get('ids');   
    $count = $this->productRepo->bulk($ids, $action);

    $message = Utils::pluralize($action.'d_product', $count);
    Session::flash('message', $message);

    return Redirect::to('products');
  }

}