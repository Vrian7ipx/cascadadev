<?php
use Illuminate\Database\Migrations\Migration;

class ConfideSetupUsersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {                             
        Schema::dropIfExists('activities');
        Schema::dropIfExists('invitations');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('products');
        Schema::dropIfExists('tax_rates');        
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('password_reminders');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('users');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('currencies');        
        Schema::dropIfExists('invoice_statuses');
        Schema::dropIfExists('countries');
        Schema::dropIfExists('timezones');        
        Schema::dropIfExists('frequencies');        
        Schema::dropIfExists('date_formats');        
        Schema::dropIfExists('datetime_formats');                
        Schema::dropIfExists('industries');
        Schema::dropIfExists('book_sales');
        Schema::dropIfExists('business_types');
        Schema::dropIfExists('prices');
        Schema::dropIfExists('price_types');
        Schema::dropIfExists('zones');
        Schema::dropIfExists('branches');
        Schema::dropIfExists('groups');
        Schema::dropIfExists('user_groups');

        Schema::create('countries', function($table)
        {           
            $table->increments('id');
            $table->string('capital', 255)->nullable();
            $table->string('citizenship', 255)->nullable();
            $table->string('country_code', 3)->default('');
            $table->string('currency', 255)->nullable();
            $table->string('currency_code', 255)->nullable();
            $table->string('currency_sub_unit', 255)->nullable();
            $table->string('full_name', 255)->nullable();
            $table->string('iso_3166_2', 2)->default('');
            $table->string('iso_3166_3', 3)->default('');
            $table->string('name', 255)->default('');
            $table->string('region_code', 3)->default('');
            $table->string('sub_region_code', 3)->default('');
            $table->boolean('eea')->default(0);                        
        });

        Schema::create('timezones', function($t)
        {
            $t->increments('id');
            $t->string('name');
            $t->string('location');
        });

        Schema::create('date_formats', function($t)
        {
            $t->increments('id');
            $t->string('format');    
            $t->string('picker_format');                    
            $t->string('label');            
        });

        Schema::create('datetime_formats', function($t)
        {
            $t->increments('id');
            $t->string('format');            
            $t->string('label');            
        });

        Schema::create('currencies', function($t)
        {
            $t->increments('id');            

            $t->string('name');
            $t->string('symbol');
            $t->string('precision');
            $t->string('thousand_separator');
            $t->string('decimal_separator');
            $t->string('code');
        });       

        Schema::create('industries', function($t)
        {
            $t->increments('id');
            $t->string('cod');
            $t->string('name');
        });

        Schema::create('business_types', function($t)
        {
            $t->increments('id');
            $t->string('cod');
            $t->string('name');
        }); 

        Schema::create('zones', function($t)
        {
            $t->increments('id');
            $t->string('region_code');
            $t->string('name');
        });

        Schema::create('price_types', function($t)
        {
            $t->increments('id');
            $t->string('name');
        });        
        
        Schema::create('accounts', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('timezone_id')->nullable();
            $t->unsignedInteger('date_format_id')->nullable();
            $t->unsignedInteger('datetime_format_id')->nullable();
            $t->unsignedInteger('currency_id')->nullable();

            $t->timestamps();
            $t->softDeletes();

            $t->string('nit');
            $t->string('name');
            $t->string('ip');
            $t->string('account_key')->unique();
            $t->timestamp('last_login');
            
            $t->string('address1');
            $t->string('address2');
            $t->string('city');
            $t->string('state');
            $t->string('postal_code');
            $t->unsignedInteger('country_id')->nullable();     
            $t->text('invoice_terms');
            $t->text('email_footer');
            $t->unsignedInteger('industry_id')->nullable();

            $t->boolean('invoice_taxes')->default(true);
            $t->boolean('invoice_item_taxes')->default(false);

            $t->foreign('timezone_id')->references('id')->on('timezones');
            $t->foreign('date_format_id')->references('id')->on('date_formats');
            $t->foreign('datetime_format_id')->references('id')->on('datetime_formats');
            $t->foreign('country_id')->references('id')->on('countries');
            $t->foreign('currency_id')->references('id')->on('currencies');
            $t->foreign('industry_id')->references('id')->on('industries');
        });

        Schema::create('branches', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id');
            $t->timestamps();
            $t->softDeletes();
            $t->string('name');
            $t->string('address1');
            $t->string('address2');
            $t->string('city');
            $t->string('state');
            $t->string('postal_code');
            $t->unsignedInteger('country_id')->nullable();     
            $t->text('invoice_terms');
            $t->text('email_footer');
            $t->unsignedInteger('industry_id')->nullable();

            $t->string('number_autho');
            $t->date('deadline');
            $t->string('key_dosage');

            $t->string('activity_pri');
            $t->string('activity_sec1');
            $t->string('activity_sec2');

            $t->string('law');

            $t->string('title');
            $t->string('subtitle');

            $t->integer('invoice_number_counter')->default(1)->nullable();
            $t->text('quote_number_prefix')->nullable();
            $t->integer('quote_number_counter')->default(1)->nullable();
            $t->boolean('share_counter')->default(false);

            $t->foreign('account_id')->references('id')->on('accounts');
            $t->foreign('country_id')->references('id')->on('countries');
            $t->foreign('industry_id')->references('id')->on('industries');
            $t->unsignedInteger('public_id')->index();
            $t->unique( array('account_id','public_id') );     

        });

        Schema::create('users', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id')->index();
            $t->unsignedInteger('price_type_id');
            $t->unsignedInteger('branch_id');
            $t->timestamps();
            $t->softDeletes();

            $t->string('first_name');
            $t->string('last_name');
            $t->string('phone');
            $t->string('username')->unique();
            $t->string('email');
            $t->string('imei');
            $t->string('password');
            $t->string('confirmation_code');
            $t->boolean('registered')->default(false);
            $t->boolean('confirmed')->default(false);
            $t->string('groups');

            $t->boolean('notify_sent')->default(true);
            $t->boolean('notify_viewed')->default(false);
            $t->boolean('notify_paid')->default(true);

            $t->foreign('account_id')->references('id')->on('accounts');
            $t->foreign('price_type_id')->references('id')->on('price_types');
            $t->foreign('branch_id')->references('id')->on('branches');

            $t->unsignedInteger('public_id');
            $t->unique( array('account_id','public_id') );
        });

        Schema::create('password_reminders', function($t)
        {
            $t->string('email');
            $t->timestamps();
            
            $t->string('token');
        }); 

        Schema::create('groups', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id');
            $t->unsignedInteger('user_id');
            $t->timestamps();
            $t->softDeletes();
            
            $t->text('code');
            $t->text('name');
            $t->text('text');

            $t->foreign('account_id')->references('id')->on('accounts');
            $t->foreign('user_id')->references('id')->on('users');
            $t->unsignedInteger('public_id');
            $t->unique( array('account_id','public_id') );

        });        

        Schema::create('clients', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('user_id');
            $t->unsignedInteger('account_id')->index();            
            $t->unsignedInteger('currency_id')->default(1);
            $t->unsignedInteger('group_id')->nullable();
            $t->timestamps();
            $t->softDeletes();

            $t->string('nit');
            $t->string('name');
            $t->string('address1');
            $t->string('address2');
            $t->string('city');
            $t->string('state');
            $t->string('postal_code');
            $t->unsignedInteger('country_id')->nullable();
            $t->string('work_phone');
            $t->text('private_notes');
            $t->decimal('balance', 13, 2);
            $t->decimal('paid_to_date', 13, 2);
            $t->timestamp('last_login')->nullable();
            $t->string('website');
            $t->unsignedInteger('industry_id')->nullable();
            $t->unsignedInteger('business_type_id')->nullable();
            $t->unsignedInteger('zone_id')->nullable();

            $t->boolean('is_deleted');

            $t->foreign('account_id')->references('id')->on('accounts');
            $t->foreign('user_id')->references('id')->on('users');
            $t->foreign('country_id')->references('id')->on('countries');       
            $t->foreign('industry_id')->references('id')->on('industries'); 
            $t->foreign('business_type_id')->references('id')->on('business_types');   
            $t->foreign('zone_id')->references('id')->on('zones');       
            $t->foreign('currency_id')->references('id')->on('currencies');
        	$t->foreign('group_id')->references('id')->on('groups');


            $t->unsignedInteger('public_id')->index();
            $t->unique( array('account_id','public_id') );
        });     

        Schema::create('contacts', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id');
            $t->unsignedInteger('user_id');

            $t->unsignedInteger('client_id')->index();
            $t->timestamps();
            $t->softDeletes();

            $t->boolean('is_primary');
            $t->boolean('send_invoice');
            $t->string('first_name');
            $t->string('last_name');
            $t->string('email');
            $t->string('phone');
            $t->timestamp('last_login');            

            $t->foreign('account_id')->references('id')->on('accounts');
            $t->foreign('client_id')->references('id')->on('clients'); 
            $t->foreign('user_id')->references('id')->on('users');


            $t->unsignedInteger('public_id');
            $t->unique( array('account_id','public_id') );
        });    

        Schema::create('user_groups', function($t)
        {
	        $t->increments('id');
            $t->unsignedInteger('account_id');
	        $t->unsignedInteger('user_id')->index();
	        $t->unsignedInteger('group_id');

            $t->timestamps();
            $t->softDeletes();

	        $t->foreign('user_id')->references('id')->on('users');
	        $t->foreign('group_id')->references('id')->on('groups');

            $t->foreign('account_id')->references('id')->on('accounts');
            $t->unsignedInteger('public_id');
            $t->unique( array('account_id','public_id') );

        });

        Schema::create('invoice_statuses', function($t)
        {
            $t->increments('id');
            $t->string('name');
        });

        Schema::create('frequencies', function($t)
        {
            $t->increments('id');
            $t->string('name');
        });

        Schema::create('invoices', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('client_id')->index();
            $t->unsignedInteger('user_id');
            $t->unsignedInteger('branch_id');
            $t->unsignedInteger('account_id')->index();
            $t->unsignedInteger('invoice_status_id')->default(1);
            $t->unsignedInteger('country_id')->nullable();  
            $t->timestamps();
            $t->softDeletes();

            $t->string('invoice_number');
            $t->float('discount');
            $t->string('po_number');
            $t->date('invoice_date')->nullable();
            $t->date('due_date')->nullable();
            $t->text('terms');
            $t->text('public_notes');
            $t->boolean('is_deleted');            
            $t->boolean('is_recurring');
            $t->unsignedInteger('frequency_id');
            $t->date('start_date')->nullable();
            $t->date('end_date')->nullable();
            $t->timestamp('last_sent_date')->nullable();  
            $t->unsignedInteger('recurring_invoice_id')->index()->nullable();

            $t->string('branch');

            $t->string('address1');
            $t->string('address2');
            $t->string('city');
            $t->string('state');
            $t->string('work_phone');
            
            $t->string('nit');
            $t->string('name');

            $t->string('number_autho');
            $t->date('deadline');
            $t->string('key_dosage');

            $t->string('activity_pri');
            $t->string('activity_sec1');
            $t->string('activity_sec2');

            $t->string('law');

            $t->string('title');
            $t->string('subtitle');

            $t->string('control_code');


            $t->string('tax_name');
            $t->decimal('tax_rate', 13, 6);

            $t->decimal('subtotal', 13, 6);//subtotal

            $t->decimal('amount', 13, 6);//total a pagar

            $t->decimal('fiscal', 13, 6);//Importe credito fiscal

            $t->decimal('balance', 13, 6);

            $t->decimal('ice', 13, 6);
        
            $t->longText('qr'); 
            
            $t->foreign('client_id')->references('id')->on('clients');
            $t->foreign('user_id')->references('id')->on('users');
            $t->foreign('account_id')->references('id')->on('accounts'); 
            $t->foreign('invoice_status_id')->references('id')->on('invoice_statuses');
            $t->foreign('recurring_invoice_id')->references('id')->on('invoices');
            $t->foreign('branch_id')->references('id')->on('branches');
            $t->foreign('country_id')->references('id')->on('countries');

            $t->unsignedInteger('public_id')->index();
            $t->unique( array('account_id','public_id') );
        });


        Schema::create('invitations', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id');
            $t->unsignedInteger('user_id');
            $t->unsignedInteger('contact_id');
            $t->unsignedInteger('invoice_id')->index();
            $t->string('invitation_key')->index()->unique();
            $t->timestamps();
            $t->softDeletes();

            $t->string('transaction_reference');
            $t->timestamp('sent_date');
            $t->timestamp('viewed_date');

            $t->foreign('user_id')->references('id')->on('users');
            $t->foreign('contact_id')->references('id')->on('contacts');
            $t->foreign('invoice_id')->references('id')->on('invoices');

            $t->unsignedInteger('public_id')->index();
            $t->unique( array('account_id','public_id') );
        });

        Schema::create('tax_rates', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id')->index();
            $t->unsignedInteger('user_id');
            $t->timestamps();
            $t->softDeletes();

            $t->string('name');
            $t->decimal('rate', 13, 2);
            
            $t->foreign('account_id')->references('id')->on('accounts'); 
            
            $t->unsignedInteger('public_id');
            $t->unique( array('account_id','public_id') );
        });

        Schema::create('products', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id')->index();
            $t->unsignedInteger('user_id');
            $t->timestamps();
            $t->softDeletes();

            $t->string('product_key');
            $t->text('notes');
            $t->string('pack_types');
            $t->boolean('ice')->default(true);

            $t->string('units');
            $t->string('cc');

            $t->string('tax_name');
            $t->decimal('tax_rate', 13, 2);

            $t->decimal('qty', 13, 2);

            $t->foreign('account_id')->references('id')->on('accounts');
            $t->unsignedInteger('public_id');
            $t->unique( array('account_id','public_id') );
        });

        Schema::create('prices', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id');
            $t->unsignedInteger('user_id');
            $t->unsignedInteger('price_type_id');
            $t->unsignedInteger('product_id')->index();
            $t->timestamps();
            $t->softDeletes();
  
            $t->decimal('cost', 13, 2);  

            $t->foreign('product_id')->references('id')->on('products'); 
            $t->foreign('user_id')->references('id')->on('users');
            $t->foreign('price_type_id')->references('id')->on('price_types');

            $t->foreign('account_id')->references('id')->on('accounts');
            $t->unsignedInteger('public_id');
            $t->unique( array('account_id','public_id') );
        });




        Schema::create('invoice_items', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id');
            $t->unsignedInteger('user_id');
            $t->unsignedInteger('invoice_id')->index();
            $t->unsignedInteger('product_id')->nullable();
            $t->timestamps();
            $t->softDeletes();

            $t->string('product_key');
            $t->text('notes');

            $t->decimal('qty', 13, 6); 

            $t->decimal('cost', 13, 6);

            $t->decimal('boni', 13, 6);   

            $t->decimal('desc', 13, 6);     

            $t->decimal('line_total', 13, 6);

            $t->string('tax_name');
            $t->decimal('tax_rate', 13, 6);

            $t->foreign('invoice_id')->references('id')->on('invoices');
            $t->foreign('product_id')->references('id')->on('products');
            $t->foreign('user_id')->references('id')->on('users');

            $t->unsignedInteger('public_id');
            $t->unique( array('account_id','public_id') );
        });        

        Schema::create('activities', function($t)
        {
            $t->increments('id');
            $t->timestamps();

            $t->unsignedInteger('account_id');
            $t->unsignedInteger('branch_id');
            $t->unsignedInteger('client_id');
            $t->unsignedInteger('user_id');
            $t->unsignedInteger('contact_id');
            $t->unsignedInteger('invoice_id');
            $t->unsignedInteger('credit_id');
            $t->unsignedInteger('invitation_id');
            
            $t->text('message');
            $t->text('json_backup');
            $t->integer('activity_type_id');            
            $t->decimal('adjustment', 13, 2);
            $t->decimal('balance', 13, 2);
            
            $t->foreign('account_id')->references('id')->on('accounts');
            $t->foreign('client_id')->references('id')->on('clients');
        });

        Schema::create('book_sales', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id');
            $t->unsignedInteger('user_id');
            $t->unsignedInteger('invoice_id')->index();
            $t->timestamps();
            $t->softDeletes();
            $t->string('nit_client');
            $t->string('rz_client');
            $t->string('number_invoice');
            $t->string('na_account');
            $t->string('date_invoice');
            $t->decimal('amount', 13, 2);
            $t->decimal('ice', 13, 2);
            $t->decimal('exempt', 13, 2);
            $t->decimal('net_amount', 13, 2);
            $t->decimal('iva', 13, 2);
            $t->string('status');
            $t->string('cc_invoice');
            
            $t->foreign('account_id')->references('id')->on('accounts');
            $t->foreign('user_id')->references('id')->on('users');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {                    
        Schema::dropIfExists('credits');        
        Schema::dropIfExists('activities');
        Schema::dropIfExists('invitations');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('products');
        Schema::dropIfExists('tax_rates');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('password_reminders');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('users');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('currencies');        
        Schema::dropIfExists('invoice_statuses');
        Schema::dropIfExists('countries');
        Schema::dropIfExists('timezones');        
        Schema::dropIfExists('frequencies');        
        Schema::dropIfExists('date_formats');        
        Schema::dropIfExists('datetime_formats');                      
        Schema::dropIfExists('industries');        
        Schema::dropIfExists('book_sales');
        Schema::dropIfExists('business_types');
        Schema::dropIfExists('prices');
        Schema::dropIfExists('price_types');
        Schema::dropIfExists('zones');
        Schema::dropIfExists('branches');
        Schema::dropIfExists('groups');
        Schema::dropIfExists('user_groups');
    }
}
