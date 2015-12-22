<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Deceased;
use Faker\Factory as Faker;

class DeceasedTest extends TestCase
{
	// use WithoutMiddleware;
	use DatabaseTransactions;

    public function testFakerFactory()
    {
    	$count = 1;
    	$user = factory(App\User::class, $count)->make();
    	$user = factory(App\User::class)->create();
    }

    public function testDeceasedIndex()
    {
        $this->withoutMiddleware();
        
    	$response = $this->call('get', '/api/v1/deceased', ['access_token' => 'RBIxvcc48fyeytw1d01206gFUNETlHqsmMHQITdN']);
    	$this->assertResponseOk($response);
    	$this->seeJson([
    				"id"	=>	24,
					"name"	=>	"test name",
					"sex"	=>	"male",
					"age"	=>	10,
					"country"	=>	"Egypt",
					"city"	=>	"Cairo",
					"death_cause"	=>	"test cause of death",
					"death_date"	=>	"2015-12-20",
					"created_at"	=>	"2015-12-20 21:06:54",
					"updated_at"	=>	"2015-12-20 21:06:54"
    			]);
    }

    public function testDeceasedCreation()
    {
    	$faker = Faker::create();

    	Deceased::Create([
    			'name'	=>	$faker->name,
    			'sex'	=>	$faker->boolean(50) ? 'male' : 'female',
    			'age'	=>	$faker->numberBetween(1, 60),
    			'country'	=>	$faker->country,
    			'city'	=>	$faker->city,
    			'death_cause'	=>	$faker->text,
    			'death_date'	=>	$faker->date('Y-m-d', 'now'),
    		]);

    	Deceased::Create([
    			'name'	=>	'test name',
    			'sex'	=>	'male',
    			'age'	=>	10,
    			'country'	=>	'Egypt',
    			'city'	=>	'Cairo',
    			'death_cause'	=>	'test cause of death',
    			'death_date'	=>	'2015-12-20'
    		]);
    	$this->seeInDatabase(
    			'deceased',
    			[
	    			'name'	=>	'test name',
	    			'sex'	=>	'male',
	    			'age'	=>	10,
	    			'country'	=>	'Egypt',
	    			'city'	=>	'Cairo',
	    			'death_cause'	=>	'test cause of death',
	    			'death_date'	=>	'2015-12-20'
	    		]
    		);
    }
}
