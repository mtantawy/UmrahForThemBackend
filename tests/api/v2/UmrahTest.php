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
    private $access_token;
    private $created_user_id;

    public function setUp()
    {
        parent::setUp();

        config(['mail.pretend' => true]);

        $this->faker = Faker::create();
        $faker = $this->faker;

        // adding a deceased creating a deceased and a umrah
        extract($this->create_user_and_get_access_token());
        $this->access_token = $access_token;
        $this->created_user_id = $created_user_id;
    }

    /**
     * @test
     * test ability to add deceased
     * @method can_add_deceased
     * @return [void]
     */
    public function can_add_deceased()
    {
        $parameters = [
            'name'      =>  $this->faker->name,
            'sex'       =>  $this->faker->boolean(50) ? 'male' : 'female',
            'age'       =>  $this->faker->numberBetween(1, 100),
            'country'   =>  $this->faker->country,
            'city'      =>  $this->faker->city,
            'death_cause'   =>  $this->faker->text,
            'death_date'    =>  $this->faker->date('Y-m-d', 'now'),
            'user_id'   =>  $this->created_user_id,
        ];
        $headers = $this->transformHeadersToServerVars([
                'Authorization'  =>  'Bearer '.$this->access_token,
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
            'id'    =>  $this->faker->randomNumber(5),
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

        $access_token = $response->getData()->access_token_info->access_token;

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

        $headers = $this->transformHeadersToServerVars([
                'Authorization'  =>  'Bearer '.$this->access_token,
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
        // create deceased by user A
        $deceased_user_a = factory(App\Deceased::class)->create(['user_id'  =>  $this->created_user_id]);
        // create deceased by user B
        $deceased_user_b = factory(App\Deceased::class)->create();
        // make a call while authenticated as user A
        $headers = $this->transformHeadersToServerVars([
                'Authorization'  =>  'Bearer '.$this->access_token,
            ]);
        $response = $this->call('GET', '/api/v2/umrah/myrequests', [], [], [], $headers);
        $this->assertResponseOk($response);

        // checking on id because any other faked data can be repeated
        // see only deceased added by user A
        // don't see deceased added by user B
        $this->seeJson(['id' => $deceased_user_a->toArray()['id']]);
        $this->dontSeeJson(['id' => $deceased_user_b->toArray()['id']]);
    }

    /**
     * @test
     * test getting all deceased info correctly
     * @method can_get_deceased_details_with_umrah
     * @return [void]
     */
    public function can_get_deceased_details_with_umrah()
    {
        // add deceased by random user
        $deceased = factory(App\Deceased::class)->create();
        // create umrah for this deceased
        $deceased->umrahs()->save(factory(App\Umrah::class, 'umrah_no_deceased_id')->make());
        // get deceased details
        $headers = $this->transformHeadersToServerVars([
                'Authorization'  =>  'Bearer '.$this->access_token,
            ]);
        $response = $this->call('GET', '/api/v2/umrah/'.$deceased->id, [], [], [], $headers);
        $this->assertResponseOk($response);
        // check for deceased details and umrah details
        $this->seeJson([
            'id'    =>  $deceased->id,
            'creator'   =>  [
                'user_id'   =>  $deceased->user->id,
                'name'      =>  $deceased->user->name,
                'email'     =>  $deceased->user->email,
                'city'      =>  $deceased->user->city,
                'country'   =>  $deceased->user->country,
                'sex'      =>  $deceased->user->sex,
            ],
            'umrahs'    =>  [
                [
                    'id'    =>  $deceased->umrahs()->first()->id,
                    'created_at'    =>  $deceased->umrahs()->first()->created_at->toDateTimeString(),
                    'updated_at'    =>  $deceased->umrahs()->first()->updated_at->toDateTimeString(),
                    'performer'     =>  [
                        'id'    =>  $deceased->umrahs()->first()->user->id,
                        'name'  =>  $deceased->umrahs()->first()->user->name,
                        'email' =>  $deceased->umrahs()->first()->user->email,
                        'city'      =>  $deceased->umrahs()->first()->user->city,
                        'country'   =>  $deceased->umrahs()->first()->user->country,
                        'sex'      =>  $deceased->umrahs()->first()->user->sex,
                    ],
                    'umrah_status'  =>  [
                        'id'    =>  $deceased->umrahs()->first()->umrahStatus->id,
                        'status'    =>  $deceased->umrahs()->first()->umrahStatus->status,
                    ],

                ]
            ],
        ]);
    }

    /**
     * @test
     * test starting an umrah for a deceased
     * @method can_start_umrah
     * @return [void]
     */
    public function can_start_umrah()
    {
        // add deceased by random user
        $deceased = factory(App\Deceased::class)->create();
        // create umrah for this deceased and authenticated user, note umrah id
        $headers = $this->transformHeadersToServerVars([
                'Authorization'  =>  'Bearer '.$this->access_token,
            ]);
        $response = $this->call('PATCH', '/api/v2/umrah/'.$deceased->id.'/updatestatus/1', [], [], [], $headers);
        $this->assertResponseOk($response);

        $umrah = json_decode($response->getContent(), true)['umrahs'][0];
        // testing if umrah status id is 1
        $this->assertEquals(1, $umrah['umrah_status']['id']);
        // get this deceased info
        $response = $this->call('GET', '/api/v2/umrah/'.$deceased->id, [], [], [], $headers);
        $this->assertResponseOk($response);
        // check for umrah id & optionally (deceased id + user id)
        $this->seeJson([
            'umrahs'    =>  [
                $umrah
            ],
        ]);
    }

    /**
     * @test
     * test changing umrah status to done, id 2
     * @method can_update_umrah_status_to_done
     * @return [void]
     */
    public function can_update_umrah_status_to_done()
    {
        // add deceased by random user
        $deceased = factory(App\Deceased::class)->create();
        // create umrah for this deceased
        $deceased->umrahs()->save(factory(App\Umrah::class, 'umrah_no_deceased_id')->make(['user_id' => $this->created_user_id]));

        // update umrah status for this deceased and authenticated user, note umrah id
        $headers = $this->transformHeadersToServerVars([
                'Authorization'  =>  'Bearer '.$this->access_token,
            ]);
        $response = $this->call('PATCH', '/api/v2/umrah/'.$deceased->id.'/updatestatus/2', [], [], [], $headers);
        $this->assertResponseOk($response);

        $umrah = json_decode($response->getContent(), true)['umrahs'][0];
        // testing if umrah status id is 2
        $this->assertEquals(2, $umrah['umrah_status']['id']);
        // get this deceased info
        $response = $this->call('GET', '/api/v2/umrah/'.$deceased->id, [], [], [], $headers);
        $this->assertResponseOk($response);
        // check for umrah id & optionally (deceased id + user id)
        $this->seeJson([
            'umrahs'    =>  [
                $umrah
            ],
        ]);
    }

    /**
     * @test
     * test changing umrah status to in progress, id 1
     * @method can_update_umrah_status_to_in_progress
     * @return [void]
     */
    public function can_update_umrah_status_to_in_progress()
    {
        // add deceased by random user
        $deceased = factory(App\Deceased::class)->create();
        // create umrah for this deceased
        $deceased->umrahs()->save(factory(App\Umrah::class, 'umrah_no_deceased_id')->make(['user_id' => $this->created_user_id]));

        // update umrah status for this deceased and authenticated user, note umrah id
        $headers = $this->transformHeadersToServerVars([
                'Authorization'  =>  'Bearer '.$this->access_token,
            ]);
        $response = $this->call('PATCH', '/api/v2/umrah/'.$deceased->id.'/updatestatus/1', [], [], [], $headers);
        $this->assertResponseOk($response);

        $umrah = json_decode($response->getContent(), true)['umrahs'][0];
        // testing if umrah status id is 1
        $this->assertEquals(1, $umrah['umrah_status']['id']);
        // get this deceased info
        $response = $this->call('GET', '/api/v2/umrah/'.$deceased->id, [], [], [], $headers);
        $this->assertResponseOk($response);
        // check for umrah id & optionally (deceased id + user id)
        $this->seeJson([
            'umrahs'    =>  [
                $umrah
            ],
        ]);
    }

    /**
     * @test
     * test changing umrah status to cancelled, removing it completely
     * @method can_update_umrah_status_to_cancelled_and_removing_it
     * @return [void]
     */
    public function can_update_umrah_status_to_cancelled_and_removing_it()
    {
        // add deceased by random user
        $deceased = factory(App\Deceased::class)->create();
        // create umrah for this deceased
        $deceased->umrahs()->save(factory(App\Umrah::class, 'umrah_no_deceased_id')->make(['user_id' => $this->created_user_id]));

        // update umrah status for this deceased and authenticated user, note umrah id
        $headers = $this->transformHeadersToServerVars([
                'Authorization'  =>  'Bearer '.$this->access_token,
            ]);
        $response = $this->call('PATCH', '/api/v2/umrah/'.$deceased->id.'/updatestatus/3', [], [], [], $headers);
        $this->assertResponseOk($response);

        $response_content = json_decode($response->getContent(), true);
        // check if message exists and is as expected.
        $this->assertTrue(isset($response_content['message']));
        $this->assertEquals('Umrah Cancelled Successfully', $response_content['message']);
        // get this deceased info
        $response = $this->call('GET', '/api/v2/umrah/'.$deceased->id, [], [], [], $headers);
        $this->assertResponseOk($response);
        // check for umrah id & optionally (deceased id + user id)
        $this->seeJson([
            'umrahs'    =>  [],
        ]);
    }

    /**
     * @test
     * test ability to edit deceased details
     * @method can_edit_deceased_details
     * @return [void]
     */
    public function can_edit_deceased_details()
    {
        // add deceased by authenticated user
        $deceased = factory(App\Deceased::class)->create(['user_id' => $this->created_user_id]);
        // edit deceased details
        $parameters = factory(App\Deceased::class)->make(['user_id' => $this->created_user_id])->toArray();
        $headers = $this->transformHeadersToServerVars([
                'Authorization'  =>  'Bearer '.$this->access_token,
            ]);
        $response = $this->call('PATCH', '/api/v2/umrah/'.$deceased->id, $parameters, [], [], $headers);
        $this->assertResponseOk($response);
        // check for new deceased details
        $this->seeJson($parameters);

        // get deceased details
        $response = $this->call('GET', '/api/v2/umrah/'.$deceased->id, [], [], [], $headers);
        $this->assertResponseOk($response);
        // check for new deceased details
        $this->seeJson($parameters);
    }

    /**
     * @test
     * test forbidding a user from edting details of deceased not owned by him
     * @method can_not_edit_deceased_not_owned_by_me
     * @return [void]
     */
    public function can_not_edit_deceased_not_owned_by_me()
    {
        // add deceased by random user
        $deceased = factory(App\Deceased::class)->create();
        // edit deceased details
        $parameters = factory(App\Deceased::class)->make(['user_id' => $deceased->user_id])->toArray();
        $headers = $this->transformHeadersToServerVars([
                'Authorization'  =>  'Bearer '.$this->access_token,
            ]);
        $response = $this->call('PATCH', '/api/v2/umrah/'.$deceased->id, $parameters, [], [], $headers);
        $this->assertResponseStatus(403); //forbidden

        // get deceased details
        $response = $this->call('GET', '/api/v2/umrah/'.$deceased->id, [], [], [], $headers);
        $this->assertResponseOk($response);
        // check for deceased details, should be unchanged
        $this->seeJson($deceased->toArray());
    }

    /**
     * @test
     * test ability to get list of umrahs performed by the user
     * @method can_get_umrahs_performed_by_me
     * @return [void]
     */
    public function can_get_umrahs_performed_by_me()
    {
        // create 2 deceaseds
        $deceased_a = factory(App\Deceased::class)->create();
        $deceased_b = factory(App\Deceased::class)->create();
        // make umrah for one deceased by random user
        $deceased_a->umrahs()->save(factory(App\Umrah::class, 'umrah_no_deceased_id')->make());
        // make umrah for the other deceased by logged in user
        $deceased_b->umrahs()->save(factory(App\Umrah::class, 'umrah_no_deceased_id')->make(['user_id'  =>  $this->created_user_id]));
        // request list of umrahs performed by me
        $headers = $this->transformHeadersToServerVars([
                'Authorization'  =>  'Bearer '.$this->access_token,
            ]);
        $response = $this->call('GET', '/api/v2/umrah/performedbyme', [], [], [], $headers);
        $this->assertResponseOk($response);
        // see the correct umrah
        $this->seeJson(['id' => $deceased_b->toArray()['id']]);
        // don't see the other umrah
        $this->dontSeeJson(['id' => $deceased_a->toArray()['id']]);
    }
}
