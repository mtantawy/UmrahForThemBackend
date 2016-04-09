<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;
use Faker\Factory as Faker;

class UmrahTest extends TestCase
{
    use DatabaseTransactions;

    private $faker;

    public function setUp()
    {
        parent::setUp();

        $this->faker = Faker::create();
        $faker = $this->faker;
    }

    /**
     * @test
     * test ability to add deceased
     * @method can_add_deceased
     * @return [void]
     */
    public function can_add_deceased()
    {
        // adding a deceased creating a deceased and a umrah
        extract($this->create_user_and_get_access_token());

        $parameters = [
            'name'      =>  $this->faker->name,
            'sex'       =>  $this->faker->boolean(50) ? 'male' : 'female',
            'age'       =>  $this->faker->numberBetween(1, 100),
            'country'   =>  $this->faker->country,
            'city'      =>  $this->faker->city,
            'death_cause'   =>  $this->faker->text,
            'death_date'    =>  $this->faker->date('Y-m-d', 'now'),
            'user_id'   =>  $created_user_id,
        ];
        $headers = $this->transformHeadersToServerVars([
                'Authorization'  =>  'Bearer '.$access_token,
            ]);
        $response = $this->call('POST', '/api/v2/umrah/', $parameters, [], [], $headers);
        $this->assertResponseOk($response);
        // check seeInDatabase
        $this->seeInDatabase(
            'deceased',
            $parameters
        );
        // modify parameters for response check
        $parameters['creator_id'] = $parameters['user_id'];
        unset($parameters['user_id']);
        $this->seeJson($parameters);
    }

    private function create_user_and_get_access_token()
    {
        $client_data = [
            'id'    =>  $this->faker->randomNumber,
            'secret'    =>  'secret',
            'name'  =>  'client_name'
        ];
        \DB::table('oauth_clients')->insert($client_data);

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
            'client_id'     =>  $client_data['id'],
            'client_secret' =>  'secret',
            'username'      =>  $user['email'],
            'password'      =>  $password,
        ];
        $response = $this->call('POST', '/api/v2/login', $login_agrs);
        $this->assertResponseOk($response);

        $access_token = $response->getData()->access_token;

        return [
            'access_token'  =>  $access_token,
            'created_user_id'   =>  $created_user->id,
        ];
    }

    /**
     * @test
     * test getting deceased with no umrahs (no umrahs with status done or in progress)
     * @method can_get_deceased_with_no_umrahs
     * @return [void]
     */
    public function can_get_deceased_with_no_umrahs()
    {
        // create deceased
        $deceased_no_umrah = factory(App\Deceased::class)->create();
        $deceased_with_umrah = factory(App\Deceased::class)->create();
        $deceased_with_umrah->umrahs()->save(factory(App\Umrah::class, 'umrah_no_deceased_id')->make());

        extract($this->create_user_and_get_access_token());

        $headers = $this->transformHeadersToServerVars([
                'Authorization'  =>  'Bearer '.$access_token,
            ]);
        $response = $this->call('GET', '/api/v2/umrah/', [], [], [], $headers);
        $this->assertResponseOk($response);

        // checking on id because any other faked data can be repeated
        $this->seeJson(['id' => $deceased_no_umrah->toArray()['id']]);
        $this->dontSeeJson(['id' => $deceased_with_umrah->toArray()['id']]);
    }

    /**
     * @test
     * test getting requests (deceased/umrahs) requested by the authenticated user
     * @method can_get_my_requests
     * @return [void]
     */
    public function can_get_my_requests()
    {
        extract($this->create_user_and_get_access_token());
        // create deceased by user A
        $deceased_user_a = factory(App\Deceased::class)->create(['user_id'  =>  $created_user_id]);
        // create deceased by user B
        $deceased_user_b = factory(App\Deceased::class)->create();
        // make a call while authenticated as user A
        $headers = $this->transformHeadersToServerVars([
                'Authorization'  =>  'Bearer '.$access_token,
            ]);
        $response = $this->call('GET', '/api/v2/umrah/myrequests', [], [], [], $headers);
        $this->assertResponseOk($response);

        // checking on id because any other faked data can be repeated
        // see only deceased added by user A
        // don't see deceased added by user B
        $this->seeJson(['id' => $deceased_user_a->toArray()['id']]);
        $this->dontSeeJson(['id' => $deceased_user_b->toArray()['id']]);
    }
}
