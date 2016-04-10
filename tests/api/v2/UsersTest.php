<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;
use Faker\Factory as Faker;

class UsersTest extends TestCase
{
    use DatabaseTransactions;

    private $faker;

    public function setUp()
    {
        parent::setUp();

        $this->faker = Faker::create();
        $faker = $this->faker;
        $this->client_id = $this->faker->randomNumber;
    }

    /**
     * @test
     * test registering a user by providing name, email, and a password, then checking it in the database
     * @method can_register_a_user
     * @return [void]
     */
    public function can_register_a_user()
    {
        $user = factory(App\User::class)->make(); // make not create to only create instances but not save in DB
        // create user with these values
        $response = $this->call('POST', '/api/v2/register', $user->toArray());
        $this->assertResponseOk($response);
        $this->seeJson([
            'name'  =>  $user->name,
            'email' =>  $user->email,
        ]);
        // check seeInDatabase
        $this->seeInDatabase(
            'users',
            [
                'name'      =>  $user->name,
                'email'     =>  $user->email,
            ]
        );
    }

    /**
     * @test
     * Test logging in a user and getting an access token
     * @method can_login_a_user_and_get_access_token
     * @return [void]
     */
    public function can_login_a_user_and_get_access_token()
    {
        \DB::table('oauth_clients')->insert(
            [
                'id'        =>  $this->client_id,
                'secret'    =>  'secret',
                'name'      =>  'client_name',
            ]
        );

        // create user in DB
        $password = bcrypt(str_random(10));
        $user = [
            'name'  =>  $this->faker->name,
            'email' =>  $this->faker->email,
            'password'  =>  bcrypt($password)
        ];
        User::Create($user);
        // try to login user
        $login_agrs = [
            'grant_type'    =>  'password',
            'client_id'     =>  $this->client_id,
            'client_secret' =>  'secret',
            'username'      =>  $user['email'],
            'password'      =>  $password,
        ];
        $response = $this->call('POST', '/api/v2/login', $login_agrs);
        $this->assertResponseOk($response);
        $this->seeJson([
            'token_type'  =>  'Bearer',
        ]);
    }

    /**
     * @test
     * Test if i can get logged in user info
     * @method can_get_logged_in_user_info
     * @param  string                      $value [description]
     * @return [type]                             [description]
     */
    public function can_get_logged_in_user_info()
    {
        \DB::table('oauth_clients')->insert(
            [
                'id'        =>  $this->client_id,
                'secret'    =>  'secret',
                'name'      =>  'client_name',
            ]
        );

        // create user in DB
        $password = bcrypt(str_random(10));
        $user = [
            'name'  =>  $this->faker->name,
            'email' =>  $this->faker->email,
            'password'  =>  bcrypt($password)
        ];
        $created_user = User::Create($user);
        // try to login user
        $login_agrs = [
            'grant_type'    =>  'password',
            'client_id'     =>  $this->client_id,
            'client_secret' =>  'secret',
            'username'      =>  $user['email'],
            'password'      =>  $password,
        ];
        $response = $this->call('POST', '/api/v2/login', $login_agrs);
        $this->assertResponseOk($response);

        $access_token = $response->getData()->access_token;

        // login user using ID
        \Auth::loginUsingId($created_user->id);

        $args = [
            'access_token' =>  $access_token,
        ];
        $response = $this->call('GET', '/api/v2/users/me', $args);
        $this->assertResponseOk($response);
        $this->seeJson([
            'id'  =>  $created_user->id,
            'name'  =>  $user['name'],
            'email' =>  $user['email'],
        ]);
    }
}
