<?php

use App\Models\Loan;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserTest extends TestCase
{
    use DatabaseMigrations;
    public $headers = [];
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->runDatabaseMigrations();
        $this->headers['Accept'] = 'application/json';
        $this->user = User::factory()->create([
            'username' => 'user',
            'name' => 'User',
            'type' => \App\Models\User::TYPE_USER,
            'password' => Hash::make('123456'),
        ]);

        $this->setHeaders($this->user);
    }

    public function setHeaders($user)
    {
        $token = JWTAuth::fromUser($user);
        $this->headers['Authorization'] = 'Bearer '.$token;
    }

    public function testGetMe()
    {
        $this->refreshApplication();
        $this->actingAs($this->user)
            ->get('/api/v1/client/auth/me', $this->headers)
            ->seeJson([
                "name" => "User",
            ]);
    }

    public function testRequestLoanSuccess()
    {
        $this->refreshApplication();
        $this->actingAs($this->user)
            ->post(
                '/api/v1/client/loans',
                [
                    'amount' => 24000000,
                    'terms' => 10,

                ],
                $this->headers
            )
            ->seeJson([
                'amount' => 24000000,
                'terms' => 10,
                'status' => PENDING
            ]);
    }

    public function testRequestLoanNotValidate()
    {
        $this->refreshApplication();
        $this->actingAs($this->user)
            ->post(
                '/api/v1/client/loans',
                [
                    'amount' => '',
                    'terms' => '',
                ],
                $this->headers
            )
            ->seeJson([
                'code' => VALIDATION_FAIL,
                'success' => false,
            ])
            ->seeJsonStructure([
                'message'
            ]);
    }

    public function testShowLoanSuccess()
    {
        $this->refreshApplication();
        $this->actingAs($this->user)
            ->get(
                '/api/v1/client/loans/1',
                $this->headers
            )
            ->seeJsonStructure([
                'message',
                'code'
            ]);
    }

    public function testShowLoanFail()
    {
        $this->refreshApplication();
        $this->actingAs($this->user)
            ->get(
                '/api/v1/client/loans/2',
                $this->headers
            )
            ->seeJson([
                'success' => false,
                'code' => 400
            ]);
    }

    public function testUpdateDayPaymentNotValidate()
    {
        $pendingLoan = Loan::factory()->create([
            'id' => 2,
            'user_id' => 1,
            'terms' => 10,
            'amount' => 20000,
            'remain_amount' => 20000,
            'status' => PENDING
        ]);
        $this->refreshApplication();
        $this->actingAs($this->user)
            ->post(
                '/api/v1/client/loans/2/update_day',
                [
                    'day' => '8',
                ],
                $this->headers
            )
            ->seeJson([
                'code' => VALIDATION_FAIL,
                'success' => false,
            ])
            ->seeJsonStructure([
                'message'
            ]);
    }

    public function testUpdateDayPaymentWithPendingStatus()
    {
        $pendingLoan = Loan::factory()->create([
            'id' => 2,
            'user_id' => 1,
            'terms' => 10,
            'amount' => 20000,
            'remain_amount' => 20000,
            'status' => PENDING
        ]);
        $this->refreshApplication();
        $this->actingAs($this->user)
            ->post(
                '/api/v1/client/loans/2/update_day',
                [
                    'day' => '2',
                ],
                $this->headers
            )
            ->seeJson([
                'code' => 400,
                'success' => false,
                'message' => __('messages.loan_not_approve')
            ]);
    }

    public function testUpdateDayPaymentNotInUser()
    {
        User::factory()->create();
        $pendingLoan = Loan::factory()->create([
            'id' => 2,
            'user_id' => 2,
            'terms' => 10,
            'amount' => 20000,
            'remain_amount' => 20000,
            'status' => PENDING
        ]);
        $this->refreshApplication();
        $this->actingAs($this->user)
            ->post(
                '/api/v1/client/loans/2/update_day',
                [
                    'day' => '2',
                ],
                $this->headers
            )
            ->seeJson([
                'code' => 400,
                'success' => false,
                'message' => __('messages.not_found',  ['model' => __('messages.tables.loan')])
            ]);
    }

    public function testUpdateDayPaymentSuccess()
    {
        $pendingLoan = Loan::factory()->create([
            'id' => 2,
            'user_id' => 1,
            'terms' => 10,
            'amount' => 20000,
            'remain_amount' => 20000,
            'status' => APPROVE
        ]);
        $this->refreshApplication();
        $this->actingAs($this->user)
            ->post(
                '/api/v1/client/loans/2/update_day',
                [
                    'day' => '2',
                ],
                $this->headers
            )
            ->seeJson([
                'code' => 200,
            ]);
    }

    public function testPaymentSuccess()
    {
        $approveLoan = Loan::factory()->create([
            'id' => 2,
            'user_id' => 1,
            'terms' => 10,
            'amount' => 20000,
            'remain_amount' => 20000,
            'payment_amount' => 2000,
            'status' => APPROVE,
            'day_of_week_payment' => \Carbon\Carbon::now()->dayOfWeek
        ]);
        $this->refreshApplication();
        $this->actingAs($this->user)
            ->post(
                '/api/v1/client/loans/2/payment',
                [
                    'amount' => '2000',
                ],
                $this->headers
            )
            ->seeJson([
                'code' => 200,
            ]);
    }

    public function testPaymentNotValidate()
    {
        $this->refreshApplication();
        $this->actingAs($this->user)
            ->post(
                '/api/v1/client/loans/1/payment',
                [
                    'amount' => '',
                ],
                $this->headers
            )
            ->seeJson([
                'code' => VALIDATION_FAIL,
                'success' => false,
            ])
            ->seeJsonStructure([
                'message'
            ]);
    }

    public function testPaymentFail()
    {
        $approveLoan = Loan::factory()->create([
            'id' => 2,
            'user_id' => 1,
            'terms' => 10,
            'amount' => 20000,
            'remain_amount' => 20000,
            'payment_amount' => 2000,
            'status' => APPROVE,
            'day_of_week_payment' => \Carbon\Carbon::now()->dayOfWeek
        ]);
        $this->refreshApplication();
        $this->actingAs($this->user)
            ->post(
                '/api/v1/client/loans/2/payment',
                [
                    'amount' => '4000',
                ],
                $this->headers
            )
            ->seeJson([
                'code' => 400,
                'success' => false,
            ]);
    }

    public function tearDown(): void
    {
//        Artisan::call('migrate:rollback');
//        parent::tearDown();
    }
}
