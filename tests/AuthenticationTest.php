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

	public function setUp()
	{
		$this->faker = Faker::create();
		$faker = $this->faker;
    	// create array of user fields' values
    	$user = [
			'name'		=>	$faker->name,
			'email'		=>	$faker->email,
			'password'	=>	$faker->password,
		];
		$this->user = $user;

		parent::setUp();
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
}
