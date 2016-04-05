<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;
use Faker\Factory as Faker;

class AuthenticationTest extends TestCase
{
	use DatabaseTransactions;
	// use WithoutMiddleware;

	private $faker;
	private $user;
	private $oauth_client;
	private $oauth_user;

	public function setUp()
	{
		parent::setUp();

		$this->faker = Faker::create();
		$faker = $this->faker;
    	// create array of user fields' values
    	$user = [
			'name'		=>	$faker->name,
			'email'		=>	$faker->email,
			'password'	=>	$faker->password,
		];
		$this->user = $user;
		// create user for logging in test
		$oauth_user = [
			'name'		=>	$faker->name,
			'email'		=>	$faker->email,
			'password'	=>	$faker->password,
		];
		$this->oauth_user = $oauth_user;
		$oauth_user['password'] = bcrypt($oauth_user['password']);
		User::Create($oauth_user);
		// create oauth_client
		$oauth_client = [
			'id'		=>	$faker->numberBetween(100,999),
			'secret'	=>	$faker->password,
			'name'		=>	$faker->name,
		];
		$this->oauth_client = $oauth_client;
		DB::table('oauth_clients')->insert($oauth_client);
	}


    /**
     * @test
     * test registering a user by providing name, email, and a password, then checking it in the database
     * @method register_a_user
     * @return [void]
     */
    public function register_a_user()
    {
    	$user = $this->user;
    	// create user with these values
    	User::Create($user);
    	// check seeInDatabase
    	$this->seeInDatabase(
    		'users',
    		[
    			'name'		=>	$user['name'],
    			'email'		=>	$user['email'],
    		]
    	);
    }

    /**
     * @test
     * test registering a user through API by providing name, email, and a password, then checking it in the database
     * @method register_a_user_via_api
     * @return [void]
     */
    public function register_a_user_through_api()
    {
    	$user = $this->user;

    	$response = $this->call('POST', '/api/v1/register', $user);
    	$this->assertResponseOk($response);
    	$this->seeJson([
					'name'	=>	$user['name'],
					'email'	=>	$user['email'],
    			]);

    }

    /**
     * @test
     * test logging in a user through api using oauth
     * @method login_through_api
     * @return [void]
     */
    public function login_through_api()
    {
    	$user = $this->oauth_user;
    	$oauth_client = $this->oauth_client;

    	$params = [
    		'grant_type'	=>	'password',
    		'client_id'		=>	$oauth_client['id'],
    		'client_secret'	=>	$oauth_client['secret'],
    		'username'		=>	$user['email'],
    		'password'		=>	$user['password'],
    	];

    	$response = $this->call('POST', '/api/v1/login', $params);
    	$this->assertResponseOk($response);
    	$this->seeJson([
			'token_type'	=>	'Bearer',
		]);
    }
}
